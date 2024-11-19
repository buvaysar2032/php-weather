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

namespace CKSource\CKFinder\ResizedImage;

use CKSource\CKFinder\{Acl\Acl,
    Acl\Permission,
    CKFinder,
    Config,
    Event\CKFinderEvent,
    Event\ResizeImageEvent,
    Exception\CKFinderException,
    Exception\FileNotFoundException,
    Exception\UnauthorizedException,
    Filesystem\Path,
    ResourceType\ResourceType};
use Exception;
use League\Flysystem\FileExistsException;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * The ThumbnailRepository class.
 *
 * A class responsible for resized image management that simplifies
 * operations on resized versions of the image file, like batch renaming/moving
 * together with the original file.
 */
class ResizedImageRepository
{
    protected Config $config;

    protected Acl $acl;

    /**
     * Event dispatcher.
     */
    protected EventDispatcher $dispatcher;

    /**
     * @param CKFinder $app The app instance.
     */
    public function __construct(protected CKFinder $app)
    {
        $this->config = $app['config'];
        $this->acl = $app['acl'];
        $this->dispatcher = $app['dispatcher'];
    }

    /**
     * Returns a resized image for the provided source file.
     *
     * If an appropriate resized version already exists, it is reused.
     *
     * @throws CKFinderException
     * @throws FileNotFoundException
     * @throws UnauthorizedException
     * @throws \League\Flysystem\FileNotFoundException
     * @throws Exception
     */
    public function getResizedImage(
        ResourceType $sourceFileResourceType,
        string $sourceFileDir,
        string $sourceFileName,
        int $requestedWidth,
        int $requestedHeight
    ): ResizedImage {
        $resizedImage = new ResizedImage(
            $this,
            $sourceFileResourceType,
            $sourceFileDir,
            $sourceFileName,
            $requestedWidth,
            $requestedHeight
        );

        if (
            !$this->acl->isAllowed(
                $sourceFileResourceType->getName(),
                $sourceFileDir,
                Permission::IMAGE_RESIZE_CUSTOM
            )
            && !$this->isSizeAllowedInConfig($requestedWidth, $requestedHeight)
        ) {
            throw new UnauthorizedException('Provided size is not allowed in images.sizes configuration');
        }

        if (!$resizedImage->exists() && $resizedImage->requestedSizeIsValid()) {
            $resizedImage->create();

            $resizeImageEvent = new ResizeImageEvent($this->app, $resizedImage);
            $this->dispatcher->dispatch($resizeImageEvent, CKFinderEvent::CREATE_RESIZED_IMAGE);

            if (!$resizeImageEvent->isPropagationStopped()) {
                $resizedImage = $resizeImageEvent->getResizedImage();
                $resizedImage->save();
            }
        }

        return $resizedImage;
    }

    /**
     * Checks if the provided image size is allowed in the configuration.
     *
     * This is checked when `Permission::IMAGE_RESIZE_CUSTOM`
     * is not allowed in the source file folder.
     *
     * @return bool `true` if the provided size is allowed in the configuration
     */
    protected function isSizeAllowedInConfig(int $width, int $height): bool
    {
        $configSizes = $this->config->get('images.sizes');

        foreach ($configSizes as $size) {
            if ($size['width'] === $width && $size['height'] === $height) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns an existing resized image.
     *
     * @throws FileNotFoundException
     * @throws Exception
     */
    public function getExistingResizedImage(
        ResourceType $sourceFileResourceType,
        string $sourceFileDir,
        string $sourceFileName,
        string $thumbnailFileName
    ): ResizedImage {
        $size = ResizedImage::getSizeFromFilename($thumbnailFileName);

        $resizedImage = new ResizedImage(
            $this,
            $sourceFileResourceType,
            $sourceFileDir,
            $sourceFileName,
            $size['width'],
            $size['height']
        );

        if (!$resizedImage->exists()) {
            throw new FileNotFoundException('Resized image not found');
        }

        $resizedImage->load();

        return $resizedImage;
    }

    public function getContainer(): CKFinder
    {
        return $this->app;
    }

    /**
     * Deletes all resized images for a given file.
     *
     * @return bool `true` if deleted
     *
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function deleteResizedImages(
        ResourceType $sourceFileResourceType,
        string $sourceFilePath,
        string $sourceFileName
    ): bool {
        $resizedImagesPath = Path::combine(
            $sourceFileResourceType->getDirectory(),
            $sourceFilePath,
            ResizedImage::DIR,
            $sourceFileName
        );

        $backend = $sourceFileResourceType->getBackend();

        if ($backend->hasDirectory($resizedImagesPath)) {
            return $backend->deleteDir($resizedImagesPath);
        }

        return false;
    }

    /**
     * Copies all resized images for a given file.
     *
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function copyResizedImages(
        ResourceType $sourceFileResourceType,
        string $sourceFilePath,
        string $sourceFileName,
        ResourceType $targetFileResourceType,
        string $targetFilePath,
        string $targetFileName
    ): void {
        $sourceResizedImagesPath = Path::combine(
            $sourceFileResourceType->getDirectory(),
            $sourceFilePath,
            ResizedImage::DIR,
            $sourceFileName
        );
        $targetResizedImagesPath = Path::combine(
            $targetFileResourceType->getDirectory(),
            $targetFilePath,
            ResizedImage::DIR,
            $targetFileName
        );

        $sourceBackend = $sourceFileResourceType->getBackend();
        $targetBackend = $targetFileResourceType->getBackend();

        if ($sourceBackend->hasDirectory($sourceResizedImagesPath)) {
            $resizedImages = $sourceBackend->listContents($sourceResizedImagesPath);

            foreach ($resizedImages as $resizedImage) {
                if (!isset($resizedImage['path'])) {
                    continue;
                }

                $resizedImageStream = $sourceBackend->readStream($resizedImage['path']);

                $sourceImageSize = ResizedImage::getSizeFromFilename($resizedImage['basename']);
                $targetImageFilename = ResizedImage::createFilename(
                    $targetFileName,
                    $sourceImageSize['width'],
                    $sourceImageSize['height']
                );

                $targetBackend->putStream(
                    Path::combine($targetResizedImagesPath, $targetImageFilename),
                    $resizedImageStream
                );
            }
        }
    }

    /**
     * Renames all resized images created for a given file.
     *
     * @throws FileExistsException
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function renameResizedImages(
        ResourceType $sourceFileResourceType,
        string $sourceFilePath,
        string $originalSourceFileName,
        string $newSourceFileName
    ): void {
        $resizedImagesDir = Path::combine($sourceFileResourceType->getDirectory(), $sourceFilePath, ResizedImage::DIR);
        $resizedImagesPath = Path::combine($resizedImagesDir, $originalSourceFileName);
        $newResizedImagesPath = Path::combine($resizedImagesDir, $newSourceFileName);

        $backend = $sourceFileResourceType->getBackend();

        if ($backend->hasDirectory($resizedImagesPath) && $backend->rename($resizedImagesPath, $newResizedImagesPath)) {
            $resizedImages = $backend->listContents($newResizedImagesPath);

            foreach ($resizedImages as $resizedImage) {
                if (!isset($resizedImage['path'])) {
                    continue;
                }

                $sourceImageSize = ResizedImage::getSizeFromFilename($resizedImage['basename']);
                $newResizedImageFilename = ResizedImage::createFilename(
                    $newSourceFileName,
                    $sourceImageSize['width'],
                    $sourceImageSize['height']
                );

                $backend->rename(
                    $resizedImage['path'],
                    Path::combine($newResizedImagesPath, $newResizedImageFilename)
                );
            }
        }
    }

    /**
     * Returns a list of resized images generated for a given file.
     *
     * @param ResourceType $sourceFileResourceType source file resource type
     * @param string $sourceFilePath source file backend-relative path
     * @param string $sourceFileName source file name
     * @param array $filterSizes array containing names of sizes defined in the `images.sizes` configuration
     */
    public function getResizedImagesList(
        ResourceType $sourceFileResourceType,
        string $sourceFilePath,
        string $sourceFileName,
        array $filterSizes = []
    ): array {
        $resizedImagesPath = Path::combine(
            $sourceFileResourceType->getDirectory(),
            $sourceFilePath,
            ResizedImage::DIR,
            $sourceFileName
        );

        $backend = $sourceFileResourceType->getBackend();

        $resizedImages = [];

        if (!$backend->hasDirectory($resizedImagesPath)) {
            return $resizedImages;
        }

        $resizedImagesFiles = array_filter(
            $backend->listContents($resizedImagesPath),
            static fn($v) => isset($v['type']) && 'file' === $v['type']
        );

        foreach ($resizedImagesFiles as $resizedImage) {
            $size = ResizedImage::getSizeFromFilename($resizedImage['basename']);

            if ($sizeName = $this->getSizeNameFromConfig($size['width'], $size['height'])) {
                if (empty($filterSizes) || in_array($sizeName, $filterSizes, true)) {
                    $resizedImages[$sizeName] = $this->createNodeValue($resizedImage);
                }

                continue;
            }

            if (empty($filterSizes)) {
                if (!isset($resizedImages['__custom'])) {
                    $resizedImages['__custom'] = [];
                }

                $resizedImages['__custom'][] = $this->createNodeValue($resizedImage);
            }
        }

        return $resizedImages;
    }

    /**
     * Returns the size name defined in the configuration, where width
     * or height are equal to those given in parameters.
     *
     * Resized images keep the original image aspect ratio.
     * When an image is resized using the size from the configuration,
     * at least one of the borders has the same length.
     *
     * @return int|string|null `true` if the size from the configuration was used
     */
    protected function getSizeNameFromConfig(int $width, int $height): int|string|null
    {
        $configSizes = $this->config->get('images.sizes');

        foreach ($configSizes as $sizeName => $size) {
            if ($size['width'] === $width || $size['height'] === $height) {
                return $sizeName;
            }
        }

        return null;
    }

    protected function createNodeValue($resizedImage)
    {
        if (isset($resizedImage['url'])) {
            return [
                'name' => $resizedImage['basename'],
                'url' => $resizedImage['url'],
            ];
        }

        return $resizedImage['basename'];
    }

    /**
     * @throws Exception
     */
    public function getResizedImageBySize(
        ResourceType $sourceFileResourceType,
        string $sourceFilePath,
        string $sourceFileName,
        int $width,
        int $height
    ): ?ResizedImage {
        $resizedImagesPath = Path::combine(
            $sourceFileResourceType->getDirectory(),
            $sourceFilePath,
            ResizedImage::DIR,
            $sourceFileName
        );

        $backend = $sourceFileResourceType->getBackend();

        if (!$backend->hasDirectory($resizedImagesPath)) {
            return null;
        }

        $resizedImagesFiles = array_filter(
            $backend->listContents($resizedImagesPath),
            static fn($v) => isset($v['type']) && 'file' === $v['type']
        );

        $thresholdPixels = $this->config->get('images.threshold.pixels');
        $thresholdPercent = (float)$this->config->get('images.threshold.percent') / 100;

        foreach ($resizedImagesFiles as $resizedImage) {
            $resizedImageSize = ResizedImage::getSizeFromFilename($resizedImage['basename']);
            $resizedImageWidth = $resizedImageSize['width'];
            $resizedImageHeight = $resizedImageSize['height'];
            if (
                $resizedImageWidth >= $width
                && ($resizedImageWidth <= $width + $thresholdPixels || $resizedImageWidth <= $width + $width * $thresholdPercent)
                && $resizedImageHeight >= $height
                && ($resizedImageHeight <= $height + $thresholdPixels || $resizedImageHeight <= $height + $height * $thresholdPercent)
            ) {
                $resizedImage = new ResizedImage(
                    $this,
                    $sourceFileResourceType,
                    $sourceFilePath,
                    $sourceFileName,
                    $resizedImageWidth,
                    $resizedImageHeight
                );

                if ($resizedImage->exists()) {
                    $resizedImage->load();

                    return $resizedImage;
                }
            }
        }

        return null;
    }
}
