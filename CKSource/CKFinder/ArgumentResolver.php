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

use ReflectionException;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;

class ArgumentResolver implements ArgumentResolverInterface
{
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
     * This method is used to inject objects to controllers.
     * It depends on arguments taken by the executed controller callable.
     *
     * Supported injected types:
     * Request             - current request object
     * CKFinder            - application object
     * EventDispatcher     - event dispatcher
     * Config              - Config object
     * Acl                 - Acl object
     * BackendManager      - BackendManager object
     * ResourceTypeFactory - ResourceTypeFactory object
     * WorkingFolder       - WorkingFolder object
     *
     * @param Request $request request object
     *
     * @return array arguments used during the command callable execution
     *
     * @throws ReflectionException
     */
    public function getArguments(
        Request $request,
        callable $controller,
        ReflectionFunctionAbstract $reflector = null
    ): array {
        $r = new ReflectionMethod($controller[0], $controller[1]);

        $parameters = $r->getParameters();

        $arguments = [];

        foreach ($parameters as $param) {
            if ($reflectionClass = $param->getName()) {
                // Don't check isInstance to avoid unnecessary instantiation
                $classShortName = ucfirst(basename(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $reflectionClass)));

                if ($classShortName === basename(
                        str_replace(['/', '\\'], DIRECTORY_SEPARATOR, get_class($this->app))
                    )) {
                    $arguments[] = $this->app;
                } elseif ($classShortName === basename(
                        str_replace(['/', '\\'], DIRECTORY_SEPARATOR, get_class($request))
                    )) {
                    $arguments[] = $request;
                } elseif ($classShortName === basename(
                        str_replace(['/', '\\'], DIRECTORY_SEPARATOR, get_class($this->app['config']))
                    )) {
                    $arguments[] = $this->app['config'];
                }

                switch ($classShortName) {
                    case 'Dispatcher':
                        $arguments[] = $this->app['dispatcher'];
                        break;
                    case 'BackendFactory':
                        $arguments[] = $this->app['backend_factory'];
                        break;
                    case 'ResourceTypeFactory':
                        $arguments[] = $this->app['resource_type_factory'];
                        break;
                    case 'Acl':
                        $arguments[] = $this->app['acl'];
                        break;
                    case 'WorkingFolder':
                        $arguments[] = $this->app['working_folder'];
                        break;
                    case 'ThumbsRepository':
                    case 'ThumbnailRepository':
                        $arguments[] = $this->app['thumbnail_repository'];
                        break;
                    case 'ResizedImageRepository':
                        $arguments[] = $this->app['resized_image_repository'];
                        break;
                    case 'Cache':
                    case 'CacheManager':
                        $arguments[] = $this->app['cache'];
                        break;
                    default:
                        break;
                }
            } else {
                $arguments[] = null;
            }
        }

        return $arguments;
    }
}
