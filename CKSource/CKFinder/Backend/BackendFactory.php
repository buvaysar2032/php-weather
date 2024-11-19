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

namespace CKSource\CKFinder\Backend;

use Aws\S3\S3Client;
use CKSource\CKFinder\{Acl\AclInterface,
    CKFinder,
    Config,
    ContainerAwareInterface,
    Exception\CKFinderException,
    Filesystem\Path};
use CKSource\CKFinder\Backend\Adapter\{AwsS3 as AwsS3Adapter, Ftp as FtpAdapter, Local as LocalFilesystemAdapter};
use CKSource\CKFinder\Backend\Adapter\Cache\Storage\Memory as MemoryCache;
use InvalidArgumentException;
use League\Flysystem\{AdapterInterface, Cached\CachedAdapter, Cached\CacheInterface, FileExistsException};

/**
 * The BackendFactory class.
 *
 * BackendFactory is responsible for the instantiation of backend adapters.
 */
class BackendFactory
{
    /**
     * The list of operations that should be tracked for a given backend type.
     */
    protected static array $trackedOperations = ['s3' => ['RenameFolder']];
    /**
     * An array of instantiated backed file systems.
     */
    protected array $backends = [];
    /**
     * Registered adapter types.
     */
    protected array $registeredAdapters = [];
    /**
     * Access Control Lists.
     */
    protected AclInterface $acl;

    /**
     * Configuration.
     */
    protected Config $config;

    /**
     * Constructor.
     *
     * @param CKFinder $app The CKFinder application container.
     */
    public function __construct(protected CKFinder $app)
    {
        $this->acl = $app['acl'];
        $this->config = $app['config'];

        $this->registerDefaultAdapters();
    }

    protected function registerDefaultAdapters(): void
    {
        $this->registerAdapter(
            'local',
            fn($backendConfig) => $this->createBackend($backendConfig, new LocalFilesystemAdapter($backendConfig))
        );

        $this->registerAdapter('ftp', function ($backendConfig) {
            $configurable = [
                'host',
                'port',
                'username',
                'password',
                'ssl',
                'timeout',
                'root',
                'permPrivate',
                'permPublic',
                'passive'
            ];

            $config = array_intersect_key($backendConfig, array_flip($configurable));

            return $this->createBackend($backendConfig, new FtpAdapter($config));
        });

        $this->registerAdapter('s3', function ($backendConfig) {
            $clientConfig = [
                'endpoint' => $backendConfig['endpoint'],
                'use_path_style_endpoint' => $backendConfig['use_path_style_endpoint'],
                'credentials' => [
                    'key' => $backendConfig['key'],
                    'secret' => $backendConfig['secret'],
                ],
                'signature_version' => $backendConfig['signature'] ?? 'v4',
                'version' => $backendConfig['version'] ?? 'latest',
            ];

            if (!empty($backendConfig['region'])) {
                $clientConfig['region'] = $backendConfig['region'];
            }

            $client = new S3Client($clientConfig);

            $filesystemConfig = [
                'visibility' => $backendConfig['visibility'] ?? 'private',
            ];

            $prefix = isset($backendConfig['root']) ? trim($backendConfig['root'], '/ ') : null;

            return $this->createBackend(
                $backendConfig,
                new AwsS3Adapter($client, $backendConfig['bucket'], $prefix),
                $filesystemConfig
            );
        });
    }

    public function registerAdapter(string $adapterName, callable $instantiationCallback): void
    {
        $this->registeredAdapters[$adapterName] = $instantiationCallback;
    }

    /**
     * Creates a backend file system.
     */
    public function createBackend(
        array $backendConfig,
        AdapterInterface $adapter,
        array $filesystemConfig = null,
        CacheInterface $cache = null
    ): Backend {
        if ($adapter instanceof ContainerAwareInterface) {
            $adapter->setContainer($this->app);
        }

        if (is_null($cache)) {
            $cache = new MemoryCache();
        }

        $cachedAdapter = new CachedAdapter($adapter, $cache);

        if (array_key_exists($backendConfig['adapter'], static::$trackedOperations)) {
            $backendConfig['trackedOperations'] = static::$trackedOperations[$backendConfig['adapter']];
        }

        return new Backend($backendConfig, $this->app, $cachedAdapter, $filesystemConfig);
    }

    /**
     * Returns the backend object for a given private directory identifier.
     *
     * @throws CKFinderException
     * @throws FileExistsException
     */
    public function getPrivateDirBackend(string $privateDirIdentifier): Backend
    {
        $privateDirConfig = $this->config->get('privateDir');

        if (!array_key_exists($privateDirIdentifier, $privateDirConfig)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Private dir with identifier %s not found. Please check configuration file.',
                    $privateDirIdentifier
                )
            );
        }

        $privateDir = $privateDirConfig[$privateDirIdentifier];

        $backend = null;

        if (is_array($privateDir) && array_key_exists('backend', $privateDir)) {
            $backend = $this->getBackend($privateDir['backend']);
        } else {
            $backend = $this->getBackend($privateDirConfig['backend']);
        }

        // Create a default .htaccess to disable access to current private directory
        $privateDirPath = $this->config->getPrivateDirPath($privateDirIdentifier);
        $htaccessPath = Path::combine($privateDirPath, '.htaccess');
        if (!$backend->has($htaccessPath)) {
            $backend->write($htaccessPath, "Order Deny,Allow\nDeny from all\n");
        }

        return $backend;
    }

    /**
     * Returns the backend object by name.
     *
     * @throws CKFinderException
     * @throws InvalidArgumentException
     */
    public function getBackend(string $backendName): Backend
    {
        if (isset($this->backends[$backendName])) {
            return $this->backends[$backendName];
        }

        $backendConfig = $this->config->getBackendNode($backendName);
        $adapterName = $backendConfig['adapter'];

        if (!isset($this->registeredAdapters[$adapterName])) {
            throw new InvalidArgumentException(
                sprintf('Backends adapter "%s" not found. Please check configuration file.', $adapterName)
            );
        }

        if (!is_callable($this->registeredAdapters[$adapterName])) {
            throw new InvalidArgumentException(
                sprintf('Backend instantiation callback for adapter "%s" is not a callable.', $adapterName)
            );
        }

        $backend = call_user_func($this->registeredAdapters[$adapterName], $backendConfig);

        if (!$backend instanceof Backend) {
            throw new CKFinderException(
                sprintf(
                    'The instantiation callback for adapter "%s" didn\'t return a valid Backend object.',
                    $adapterName
                )
            );
        }

        $this->backends[$backendName] = $backend;

        return $backend;
    }
}
