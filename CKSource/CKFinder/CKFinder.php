<?php

/*
 * CKFinder
 * ========
 * https://ckeditor.com/ckfinder/
 * Copyright (c) 2007-2023, CKSource Holding sp. z o.o. All rights reserved.
 *
 * The software, this file and its contents are subject to the CKFinder
 * License. Please read the license.txt file before using, installing, copying,
 * modifying or distribute this file or part of its contents. The contents of
 * this file is part of the Source Code of CKFinder.
 */

namespace CKSource\CKFinder;

use CKSource\CKFinder\{Authentication\AuthenticationInterface,
    Authentication\CallableAuthentication,
    Backend\BackendFactory,
    Event\AfterCommandEvent,
    Event\CKFinderEvent,
    Exception\CKFinderException,
    Exception\InvalidCsrfTokenException,
    Exception\InvalidPluginException,
    Operation\OperationManager,
    Plugin\PluginInterface,
    Request\Transformer\TransformerInterface,
    ResizedImage\ResizedImageRepository,
    ResourceType\ResourceTypeFactory,
    Response\JsonResponse,
    Thumbnail\ThumbnailRepository};
use CKSource\CKFinder\Acl\{Acl, User\SessionRoleContext};
use CKSource\CKFinder\Cache\{Adapter\BackendAdapter, CacheManager};
use CKSource\CKFinder\Filesystem\{Folder\WorkingFolder, Path};
use CKSource\CKFinder\Request\Transformer\JsonTransformer;
use CKSource\CKFinder\Security\Csrf\{DoubleSubmitCookieTokenValidator, TokenValidatorInterface};
use Exception;
use League\Flysystem\Adapter\Local as LocalFSAdapter;
use Monolog\Handler\{ErrorLogHandler, FirePHPHandler, StreamHandler};
use Monolog\Logger;
use Pimple\Container;
use Symfony\Component\EventDispatcher\{EventDispatcher, EventSubscriberInterface};
use Symfony\Component\HttpFoundation\{Request, RequestStack, Response};
use Symfony\Component\HttpKernel\{Event\RequestEvent,
    Event\ResponseEvent,
    Event\ViewEvent,
    HttpKernel,
    HttpKernelInterface,
    KernelEvents};
use Throwable;

/**
 * The main CKFinder class.
 *
 * It is based on <a href="http://pimple.sensiolabs.org/">Pimple</a>
 * so it also serves as a dependency injection container.
 */
class CKFinder extends Container implements HttpKernelInterface
{
    public const VERSION = '3.5.1.2';

    public const COMMANDS_NAMESPACE = 'CKSource\\CKFinder\\Command\\';
    public const PLUGINS_NAMESPACE = 'CKSource\\CKFinder\\Plugin\\';

    public const CHARS = '123456789ABCDEFGHJKLMNPQRSTUVWXYZ';

    protected array $plugins = [];

    protected bool $booted = false;

    /**
     * Constructor.
     *
     * @param array|string $config an array containing configuration options or a path
     *                             to the configuration file
     *
     * @see config.php
     */
    public function __construct($config)
    {
        parent::__construct();

        $app = $this;

        $this['config'] = static fn() => new Config($config);

        $this['authentication'] = static fn() => new CallableAuthentication($app['config']->get('authentication'));

        $this['exception_handler'] = static fn() => new ExceptionHandler(
            $app['translator'],
            $app['debug'],
            $app['logger']
        );

        $this['dispatcher'] = function () use ($app) {
            $eventDispatcher = new EventDispatcher();

            $eventDispatcher->addListener(KernelEvents::REQUEST, [$this, 'handleOptionsRequest'], 512);
            $eventDispatcher->addListener(KernelEvents::VIEW, [$this, 'createResponse'], -512);
            $eventDispatcher->addListener(KernelEvents::RESPONSE, [$this, 'afterCommand'], -512);

            $eventDispatcher->addSubscriber($app['exception_handler']);

            return $eventDispatcher;
        };

        $this['command_resolver'] = static function () use ($app) {
            $commandResolver = new CommandResolver($app);
            $commandResolver->setCommandsNamespace(self::COMMANDS_NAMESPACE);
            $commandResolver->setPluginsNamespace(self::PLUGINS_NAMESPACE);

            return $commandResolver;
        };

        $this['argument_resolver'] = static fn() => new ArgumentResolver($app);

        $this['request_stack'] = static fn() => new RequestStack();

        $this['request_transformer'] = static fn() => new JsonTransformer();

        $this['working_folder'] = function () use ($app) {
            $workingFolder = new WorkingFolder($app);

            $this['dispatcher']->addSubscriber($workingFolder);

            return $workingFolder;
        };

        $this['operation'] = static fn() => new OperationManager($app);

        $this['kernel'] = static fn() => new HttpKernel(
            $app['dispatcher'],
            $app['command_resolver'],
            $app['request_stack'],
            $app['argument_resolver']
        );

        $this['acl'] = static function () use ($app) {
            $config = $app['config'];

            $roleContext = new SessionRoleContext($config->get('roleSessionVar'));

            $acl = new Acl($roleContext);
            $acl->setRules($config->get('accessControl'));

            return $acl;
        };

        $this['backend_factory'] = static fn() => new BackendFactory($app);

        $this['resource_type_factory'] = static fn() => new ResourceTypeFactory($app);

        $this['thumbnail_repository'] = static fn() => new ThumbnailRepository($app);

        $this['resized_image_repository'] = static fn() => new ResizedImageRepository($app);

        $this['cache'] = static function () use ($app) {
            $cacheBackend = $app['backend_factory']->getPrivateDirBackend('cache');
            $cacheDir = $app['config']->getPrivateDirPath('cache') . '/data';

            return new CacheManager(new BackendAdapter($cacheBackend, $cacheDir));
        };

        $this['translator'] = static fn() => new Translator();

        $this['debug'] = $app['config']->get('debug');

        $this['logger'] = static function () use ($app) {
            $logger = new Logger('CKFinder');

            if ($app['config']->isDebugLoggerEnabled('firephp')) {
                $logger->pushHandler(new FirePHPHandler());
            }

            if ($app['config']->isDebugLoggerEnabled('error_log')) {
                $logger->pushHandler(new ErrorLogHandler());
            }

            return $logger;
        };

        if ($app['config']->get('csrfProtection')) {
            $this['csrf_token_validator'] = static fn() => new DoubleSubmitCookieTokenValidator();
        }
    }

    /**
     * A handler for the OPTIONS HTTP method.
     *
     * If the request HTTP method is OPTIONS, it returns an empty response with
     * extra headers defined in the configuration.
     * This handler is executed very early, so if required, the response is set
     * even before the controller for the current request is resolved.
     */
    public function handleOptionsRequest(RequestEvent $event): void
    {
        if ($event->getRequest()->isMethod(Request::METHOD_OPTIONS)) {
            $event->setResponse(new Response('', Response::HTTP_OK, $this->getExtraHeaders()));
        }
    }

    /**
     * Returns an array of extra headers defined in the `headers` configuration option.
     *
     * @return array an array of headers
     */
    protected function getExtraHeaders(): array
    {
        $headers = $this['config']->get('headers');

        return is_array($headers) ? $headers : [];
    }

    /**
     * Creates a response.
     */
    public function createResponse(ViewEvent $event): void
    {
        /** @var EventDispatcher $dispatcher */
        $dispatcher = $this['dispatcher'];

        $commandName = $event->getRequest()->get('command');
        $eventName = CKFinderEvent::CREATE_RESPONSE_PREFIX . lcfirst($commandName);
        $dispatcher->dispatch($event, $eventName);

        $controllerResult = $event->getControllerResult();
        $event->setResponse(new JsonResponse($controllerResult));
    }

    /**
     * Fires `afterCommand` events.
     */
    public function afterCommand(ResponseEvent $event): void
    {
        /** @var EventDispatcher $dispatcher */
        $dispatcher = $this['dispatcher'];

        $commandName = $event->getRequest()->get('command');
        $eventName = CKFinderEvent::AFTER_COMMAND_PREFIX . lcfirst($commandName);
        $afterCommandEvent = new AfterCommandEvent($this, $commandName, $event->getResponse());
        $dispatcher->dispatch($afterCommandEvent, $eventName);

        // #161 Clear any garbage from the output
        Response::closeOutputBuffers(0, false);

        $response = $afterCommandEvent->getResponse();
        $response->headers->add($this->getExtraHeaders());

        $event->setResponse($response);
    }

    /**
     * Registers a listener for an event.
     *
     * @param string   $eventName event name
     * @param callable $listener  listener callable
     * @param int      $priority  priority
     */
    public function on(string $eventName, callable $listener, int $priority = 0): void
    {
        /** @var EventDispatcher $dispatcher */
        $dispatcher = $this['dispatcher'];

        $dispatcher->addListener($eventName, $listener, $priority);
    }

    /**
     * Main method used to handle a request by CKFinder.
     *
     * @param Request|null $request request object
     *
     * @throws Exception|Throwable
     */
    public function run(Request $request = null): void
    {
        $request = $request ?? Request::createFromGlobals();

        /** @var HttpKernel $kernel */
        $kernel = $this['kernel'];

        $response = $this->handle($request);
        $response->send();

        $kernel->terminate($request, $response);
    }

    /**
     * @throws Throwable
     */
    public function handle(
        Request $request,
        int $type = HttpKernelInterface::MAIN_REQUEST,
        bool $catch = true
    ): Response {
        /** @var HttpKernel $kernel */
        $kernel = $this['kernel'];

        /** @var TransformerInterface $requestTransformer */
        $requestTransformer = $this['request_transformer'];

        if ($requestTransformer) {
            $request = $requestTransformer->transform($request);
        }

        // Handle early exceptions
        if (!$this->booted) {
            try {
                $this->boot($request);
            } catch (Exception $e) {
                $this['request_stack']->push($request);
                $kernel->terminateWithException($e);
                exit;
            }
        }

        return $kernel->handle($request, $type, $catch);
    }

    /**
     * Prepares application environment before the Request is dispatched.
     *
     * @throws CKFinderException
     * @throws InvalidCsrfTokenException
     * @throws Exception
     */
    public function boot(Request $request): void
    {
        if ($this->booted) {
            return;
        }

        $this->booted = true;

        $config = $this['config'];

        $this->checkRequirements();

        if ($config->get('debug') && $config->isDebugLoggerEnabled('ckfinder_log')) {
            $this->registerStreamLogger();
        }

        $this->checkAuth();

        if ($config->get('csrfProtection')) {
            $this->checkCsrfToken($request);
        }

        $this->registerPlugins();

        $commandName = (string)$request->query->get('command');

        if ($config->get('sessionWriteClose') && 'Init' !== $commandName && PHP_SESSION_ACTIVE === session_status()) {
            session_write_close();
        }
    }

    /**
     * Checks PHP requirements.
     *
     * @throws CKFinderException
     */
    protected function checkRequirements(): void
    {
        $errorMessage = 'The PHP installation does not meet the minimum system requirements for CKFinder. %s Please refer to CKFinder documentation for more details.';

        if (version_compare(PHP_VERSION, '5.6.0') < 0) {
            throw new CKFinderException(
                sprintf($errorMessage, 'Your PHP version is too old. CKFinder 3.x requires PHP 5.6+.'),
                Error::CUSTOM_ERROR
            );
        }

        $missingExtensions = [];

        if (!function_exists('gd_info')) {
            $missingExtensions[] = 'GD';
        }

        if (!function_exists('finfo_file')) {
            $missingExtensions[] = 'Fileinfo';
        }

        if (!empty($missingExtensions)) {
            throw new CKFinderException(
                sprintf($errorMessage, 'Missing PHP extensions: ' . implode(', ', $missingExtensions) . '.'),
                Error::CUSTOM_ERROR
            );
        }
    }

    /**
     * Registers a stream handler for error logging.
     *
     * @throws Exception
     */
    public function registerStreamLogger(): void
    {
        $app = $this;

        /** @var \CKSource\CKFinder\Backend\Backend $logsBackend */
        $logsBackend = $app['backend_factory']->getPrivateDirBackend('logs');

        $adapter = $logsBackend->getBaseAdapter();

        if ($adapter instanceof LocalFSAdapter) {
            $logsDir = $app['config']->getPrivateDirPath('logs');

            $errorLogPath = Path::combine($logsDir, 'error.log');

            $logPath = $adapter->applyPathPrefix($errorLogPath);

            $app['logger']->pushHandler(new StreamHandler($logPath));
        }
    }

    /**
     * Checks authentication.
     *
     * @throws CKFinderException
     */
    public function checkAuth(): void
    {
        /** @var AuthenticationInterface $authentication */
        $authentication = $this['authentication'];

        if (!$authentication->authenticate()) {
            ini_set('display_errors', 0);

            throw new CKFinderException('CKFinder is disabled', Error::CONNECTOR_DISABLED);
        }
    }

    /**
     * Validates the CSRF token.
     *
     * @throws InvalidCsrfTokenException
     */
    public function checkCsrfToken(Request $request): void
    {
        $ignoredMethods = [Request::METHOD_GET, Request::METHOD_OPTIONS];

        if (in_array($request->getMethod(), $ignoredMethods, true)) {
            return;
        }

        /** @var TokenValidatorInterface $csrfTokenValidator */
        $csrfTokenValidator = $this['csrf_token_validator'];

        if (!$csrfTokenValidator->validate($request)) {
            throw new InvalidCsrfTokenException();
        }
    }

    /**
     * Registers plugins defined in the configuration file.
     *
     * @throws \CKSource\CKFinder\Exception\InvalidPluginException
     */
    protected function registerPlugins(): void
    {
        $pluginsEntries = $this['config']->get('plugins');
        $pluginsDirectory = $this['config']->get('pluginsDirectory');

        foreach ($pluginsEntries as $pluginInfo) {
            if (is_array($pluginInfo)) {
                $pluginName = ucfirst($pluginInfo['name']);
                if (isset($pluginInfo['path'])) {
                    require_once $pluginInfo['path'];
                }
            } else {
                $pluginName = ucfirst($pluginInfo);
            }

            $pluginPath = Path::combine($pluginsDirectory, $pluginName, $pluginName . '.php');

            if (file_exists($pluginPath) && is_readable($pluginPath)) {
                require_once $pluginPath;
            }

            $pluginClassName = self::PLUGINS_NAMESPACE . $pluginName . '\\' . $pluginName;

            if (!class_exists($pluginClassName)) {
                throw new InvalidPluginException(
                    sprintf('CKFinder plugin "%s" not found (%s)', $pluginName, $pluginClassName),
                    ['pluginName' => $pluginName]
                );
            }

            $pluginObject = new $pluginClassName($this);

            if ($pluginObject instanceof PluginInterface) {
                $this->registerPlugin($pluginObject);
            } else {
                throw new InvalidPluginException(
                    sprintf('CKFinder plugin class must implement %sPluginInterface', self::PLUGINS_NAMESPACE),
                    ['pluginName' => $pluginName]
                );
            }
        }
    }

    /**
     * Registers the plugin.
     */
    public function registerPlugin(PluginInterface $plugin): void
    {
        $plugin->setContainer($this);

        $pluginNameParts = explode('\\', get_class($plugin));
        $pluginName = end($pluginNameParts);

        $this['config']->extend($pluginName, $plugin->getDefaultConfig());

        if ($plugin instanceof EventSubscriberInterface) {
            $this['dispatcher']->addSubscriber($plugin);
        }

        $this->plugins[$pluginName] = $plugin;
    }

    /**
     * Returns the BackedFactory service.
     */
    public function getBackendFactory(): BackendFactory
    {
        return $this['backend_factory'];
    }

    /**
     * Returns the ACL service.
     */
    public function getAcl(): Acl
    {
        return $this['acl'];
    }

    /**
     * Returns the current WorkingFolder object.
     */
    public function getWorkingFolder(): WorkingFolder
    {
        return $this['working_folder'];
    }

    /**
     * Shorthand for debugging using the defined logger.
     */
    public function debug(string $message, array $context = []): void
    {
        $logger = $this['logger'];

        $logger?->debug($message, $context);
    }

    /**
     * Returns an array containing all registered plugins.
     *
     * @return array array of PluginInterface-s
     */
    public function getPlugins(): array
    {
        return $this->plugins;
    }

    /**
     * Returns a plugin by the name.
     *
     * @param string $name plugin name
     */
    public function getPlugin(string $name): ?PluginInterface
    {
        return $this->plugins[$name] ?? null;
    }

    /**
     * Returns the resized image repository.
     */
    public function getResizedImageRepository(): ResizedImageRepository
    {
        return $this['resized_image_repository'];
    }

    /**
     * Returns the connector URL based on the current request.
     *
     * @param bool|true $full if set to `true`, the returned URL contains the scheme and host
     */
    public function getConnectorUrl(bool $full = true): string
    {
        $request = $this->getRequest();

        return ($full ? $request->getSchemeAndHttpHost() : '') . $request->getBaseUrl();
    }

    /**
     * Returns the current request object.
     */
    public function getRequest(): Request
    {
        /** @var RequestStack $requestStack */
        $requestStack = $this['request_stack'];

        return $requestStack->getCurrentRequest();
    }
}
