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

use CKSource\CKFinder\{Backend\Backend, CKFinder, ResizedImage\ResizedImageRepository, Thumbnail\ThumbnailRepository};
use CKSource\CKFinder\Filesystem\File\File;

class ResourceType
{
    protected CKFinder $app;

    public function __construct(
        protected string $name,
        protected array $configNode,
        protected Backend $backend,
        protected ThumbnailRepository $thumbnailRepository,
        protected ResizedImageRepository $resizedImageRepository
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDirectory()
    {
        return $this->configNode['directory'];
    }

    public function getBackend(): Backend
    {
        return $this->backend;
    }

    public function getThumbnailRepository(): ThumbnailRepository
    {
        return $this->thumbnailRepository;
    }

    public function getResizedImageRepository(): ResizedImageRepository
    {
        return $this->resizedImageRepository;
    }

    public function getMaxSize()
    {
        return $this->configNode['maxSize'];
    }

    public function getAllowedExtensions()
    {
        return $this->configNode['allowedExtensions'];
    }

    public function getDeniedExtensions()
    {
        return $this->configNode['deniedExtensions'];
    }

    public function getLabel()
    {
        return $this->configNode['label'] ?? null;
    }

    public function isLazyLoaded(): bool
    {
        return isset($this->configNode['lazyLoad']) && $this->configNode['lazyLoad'];
    }

    public function isAllowedExtension(string $extension): bool
    {
        $extension = strtolower(ltrim($extension, '.'));

        if ($extension === strtolower(File::NO_EXTENSION)) {
            return false;
        }

        if (!$extension) {
            $extension = strtolower(File::NO_EXTENSION);
        }

        $allowed = $this->configNode['allowedExtensions'];
        $denied = $this->configNode['deniedExtensions'];

        return !((!empty($allowed) && !in_array($extension, $allowed, true)) ||
            (!empty($denied) && in_array($extension, $denied, true)));
    }

    /**
     * Returns the resource type hash.
     */
    public function getHash(): string
    {
        return substr(
            md5(
                $this->configNode['name'] . $this->configNode['backend'] . $this->configNode['directory'] .
                $this->backend->getBaseUrl() . $this->backend->getRootDirectory()
            ),
            0,
            16
        );
    }
}
