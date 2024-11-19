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

namespace CKSource\CKFinder\ResourceType;

use CKSource\CKFinder\{Backend\BackendFactory,
    CKFinder,
    Config,
    Exception\CKFinderException,
    ResizedImage\ResizedImageRepository,
    Thumbnail\ThumbnailRepository};
use Pimple\Container;

class ResourceTypeFactory extends Container
{
    protected Config $config;

    protected BackendFactory $backendFactory;

    protected ThumbnailRepository $thumbnailRepository;

    protected ResizedImageRepository $resizedImageRepository;

    /**
     * @param CKFinder $app The app instance.
     */
    public function __construct(protected CKFinder $app)
    {
        parent::__construct();
        $this->config = $app['config'];
        $this->backendFactory = $app['backend_factory'];
        $this->thumbnailRepository = $app['thumbnail_repository'];
        $this->resizedImageRepository = $app['resized_image_repository'];
    }

    /**
     * Returns the resource type object with a given name.
     *
     * @param string $name resource type name
     *
     * @throws CKFinderException
     */
    public function getResourceType(string $name): ResourceType
    {
        if (!$this->offsetExists($name)) {
            $resourceTypeConfig = $this->config->getResourceTypeNode($name);
            $backend = $this->backendFactory->getBackend($resourceTypeConfig['backend']);

            $this[$name] = new ResourceType($name, $resourceTypeConfig, $backend, $this->thumbnailRepository, $this->resizedImageRepository);
        }

        return $this[$name];
    }
}
