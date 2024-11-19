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

use CKSource\CKFinder\{Command\CommandAbstract,
    Event\BeforeCommandEvent,
    Event\CKFinderEvent,
    Exception\InvalidCommandException,
    Exception\MethodNotAllowedException};
use Exception;
use ReflectionClass;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

/**
 * The command resolver class.
 *
 * The purpose of this class is to resolve which CKFinder command should be executed
 * for the current request. This process is based on a value passed in the
 * <code>$_GET['command']</code> request variable.
 */
class CommandResolver implements ControllerResolverInterface
{
    /**
     * The name of the method to execute in commands classes.
     */
    public const COMMAND_EXECUTE_METHOD = 'execute';

    /**
     * The commands class namespace.
     */
    protected string $commandsNamespace;

    /**
     * The plugins class namespace.
     */
    protected string $pluginsNamespace;

    /**
     * Constructor.
     *
     * @param CKFinder $app The app instance.
     */
    public function __construct(
        protected CKFinder $app
    ) {
    }

    /**
     * Sets the namespace used to resolve commands.
     */
    public function setCommandsNamespace(string $namespace): void
    {
        $this->commandsNamespace = $namespace;
    }

    /**
     * Sets the namespace used to resolve plugin commands.
     */
    public function setPluginsNamespace(string $namespace): void
    {
        $this->pluginsNamespace = $namespace;
    }

    /**
     * This method looks for a 'command' request attribute. An appropriate class
     * is then instantiated and used to build callable.
     *
     * @param Request $request current Request instance
     *
     * @return callable|false callable built to execute the command
     *
     * @throws InvalidCommandException if a valid command cannot be found
     * @throws MethodNotAllowedException if a command was called using an invalid HTTP method
     * @throws Exception
     */
    public function getController(Request $request): callable|false
    {
        $commandName = ucfirst((string)$request->get('command'));

        /** @var Command\CommandAbstract $commandObject */
        $commandObject = null;

        // First check for regular command class
        $commandClassName = $this->commandsNamespace . $commandName;

        if (class_exists($commandClassName)) {
            $reflectedClass = new ReflectionClass($commandClassName);
            if (!$reflectedClass->isInstantiable()) {
                throw new InvalidCommandException(
                    sprintf('CKFinder command class %s is not instantiable', $commandClassName)
                );
            }
            $commandObject = new $commandClassName($this->app);
        }

        // If not found - check if command plugin with given name exists
        if (is_null($commandObject)) {
            $plugin = $this->app->getPlugin($commandName);
            if ($plugin instanceof CommandAbstract) {
                $commandObject = $plugin;
            }
        }

        if (is_null($commandObject)) {
            throw new InvalidCommandException(sprintf('CKFinder command %s not found', $commandName));
        }

        if (!$commandObject instanceof CommandAbstract) {
            throw new InvalidCommandException(
                sprintf('CKFinder command must be a subclass of CommandAbstract (%s given)', get_class($commandObject))
            );
        }

        if (!method_exists($commandObject, self::COMMAND_EXECUTE_METHOD)) {
            throw new InvalidCommandException(
                sprintf("CKFinder command class %s doesn't contain required 'execute' method", $commandClassName)
            );
        }

        if ($commandObject->getRequestMethod() !== $request->getMethod()) {
            throw new MethodNotAllowedException(
                sprintf(
                    'CKFinder command %s expects to be called with %s HTTP request. Actual method: %s',
                    $commandName,
                    $commandObject->getRequestMethod(),
                    $request->getMethod()
                )
            );
        }

        /** @var EventDispatcher $dispatcher */
        $dispatcher = $this->app['dispatcher'];
        $beforeCommandEvent = new BeforeCommandEvent($this->app, $commandName, $commandObject);

        $eventName = CKFinderEvent::BEFORE_COMMAND_PREFIX . lcfirst($commandName);

        $dispatcher->dispatch($beforeCommandEvent, $eventName);

        $commandObject = $beforeCommandEvent->getCommandObject();

        $commandObject->checkPermissions();

        return [$commandObject, self::COMMAND_EXECUTE_METHOD];
    }
}
