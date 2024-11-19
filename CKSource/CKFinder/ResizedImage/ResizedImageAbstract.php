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

use CKSource\CKFinder\{Backend\Backend, Exception\CKFinderException, Filesystem\Path, Image, ResourceType\ResourceType};
use JetBrains\PhpStorm\Pure;
use League\Flysystem\FileNotFoundException;

abstract class ResizedImageAbstract
{
    /**
     * The Backend where resized images are stored. By default,
     * it points to the `dir.thumbs` local file system directory.
     */
    protected Backend $backend;

    /**
     * Thumbnail file name. For example the name of the resized image generated
     * for a file `example.jpg` may look like `example__300x300.jpg`.
     */
    protected string $resizedImageFileName;

    /**
     * Thumbnail image binary data.
     */
    protected ?string $resizedImageData = null;

    /**
     * Thumbnail image size in bytes.
     */
    protected int $resizedImageSize;

    /**
     * Thumbnail image MIME type.
     */
    protected string $resizedImageMimeType;

    /**
     * Timestamp with the last modification time of the resized image.
     */
    protected int $timestamp;

    /**
     * @param ResourceType $sourceFileResourceType The source file resource type object.
     * @param string       $sourceFileDir          The source file directory path.
     * @param string       $sourceFileName         The source file name.
     * @param int          $requestedWidth         The width requested for this resized image.
     * @param int          $requestedHeight        The height requested for this resized image.
     */
    #[Pure]
    public function __construct(
        protected ResourceType $sourceFileResourceType,
        protected string $sourceFileDir,
        protected string $sourceFileName,
        protected int $requestedWidth,
        protected int $requestedHeight
    ) {
        $this->backend = $sourceFileResourceType->getBackend();
    }

    /**
     * Returns the resized image resource type.
     */
    public function getResourceType(): ResourceType
    {
        return $this->sourceFileResourceType;
    }

    /**
     * Returns a timestamp of the last modification of this resized image.
     *
     * @return int timestamp
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * Returns the resized image size in bytes.
     */
    public function getSize(): int
    {
        return $this->resizedImageSize;
    }

    /**
     * Returns the resized image binary data.
     *
     * @return string binary image date
     */
    public function getImageData(): string
    {
        return $this->resizedImageData;
    }

    /**
     * Sets the image data.
     *
     * @param string $imageData binary image data
     *
     * @throws CKFinderException
     */
    public function setImageData(string $imageData): void
    {
        $image = Image::create($imageData);

        $this->resizedImageSize = strlen($imageData);
        $this->resizedImageMimeType = $image->getMimeType();
        $this->resizedImageData = $imageData;

        unset($image);
    }

    /**
     * Checks if the resized image already exists.
     */
    public function exists(): bool
    {
        return $this->backend->has($this->getFilePath());
    }

    /**
     * Returns the backend-relative resized image file path.
     */
    public function getFilePath(): string
    {
        return Path::combine($this->getDirectory(), $this->getFileName());
    }

    /**
     * Returns a directory path for the resized image.
     */
    abstract public function getDirectory();

    /**
     * Returns the resized image file name.
     */
    public function getFileName(): string
    {
        return $this->resizedImageFileName;
    }

    /**
     * Saves the resized image in the backend.
     *
     * @return bool `true` if saved successfully
     */
    public function save(): bool
    {
        if (!$this->backend->hasDirectory($this->getDirectory())) {
            $this->backend->createDir($this->getDirectory());
        }

        $saved = $this->backend->put(
            $this->getFilePath(), $this->resizedImageData, ['mimetype' => $this->getMimeType()]
        );

        if ($saved) {
            $this->timestamp = time();
        }

        return $saved;
    }

    /**
     * Returns the resized image MIME type.
     */
    public function getMimeType(): string
    {
        return $this->resizedImageMimeType;
    }

    /**
     * Loads an existing resized image from a backend.
     * @throws FileNotFoundException
     */
    public function load(): void
    {
        $thumbnailMetadata = $this->backend->getWithMetadata($this->getFilePath(), ['mimetype', 'timestamp']);
        $this->timestamp = $thumbnailMetadata['timestamp'];
        $this->resizedImageSize = $thumbnailMetadata['size'];
        $this->resizedImageMimeType = $thumbnailMetadata['mimetype'];

        $this->resizedImageData = $this->backend->read($this->getFilePath());
    }

    /**
     * Returns image data stream.
     *
     * @return resource|bool|false
     *
     * @throws CKFinderException
     */
    public function readStream()
    {
        if (is_null($this->resizedImageData)) {
            throw new CKFinderException(
                'The resized image was not loaded from a backend yet. Please use ResizedImage::load() first.'
            );
        }

        // The image should be already loaded the memory, no need to read stream from backend
        $stream = fopen('php://temp', 'rb+');
        fwrite($stream, $this->resizedImageData);
        rewind($stream);

        return $stream;
    }

    /**
     * Creates the resized image.
     */
    abstract public function create();
}
