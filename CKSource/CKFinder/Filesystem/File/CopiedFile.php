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

namespace CKSource\CKFinder\Filesystem\File;

use CKSource\CKFinder\{Backend\Backend,
    CKFinder,
    Error,
    Exception\InvalidRequestException,
    Filesystem\Path,
    ResourceType\ResourceType};
use CKSource\CKFinder\Filesystem\Folder\WorkingFolder;
use Exception;
use League\Flysystem\FileNotFoundException;

/**
 * The CopiedFile class.
 *
 * Represents a copied file.
 */
class CopiedFile extends ExistingFile
{
    protected WorkingFolder $targetFolder;

    /**
     * Defines copy options in case a file already exists
     * in the target directory:
     * - autorename - Renames the current file (see File::autorename()).
     * - overwrite - Overwrites the existing file.
     */
    protected string $copyOptions;

    /**
     * Constructor.
     *
     * @param string       $sourceFileName source file name
     * @param string       $folder         copied source file resource type relative path
     * @param ResourceType $resourceType   source file resource type
     */
    public function __construct(
        protected string $sourceFileName,
        string $folder,
        ResourceType $resourceType,
        CKFinder $app
    ) {
        $this->targetFolder = $app['working_folder'];

        parent::__construct($sourceFileName, $folder, $resourceType, $app);
    }

    /**
     * Sets copy options.
     *
     * @see CopiedFile::$copyOptions
     */
    public function setCopyOptions(string $copyOptions): void
    {
        $this->copyOptions = $copyOptions;
    }

    /**
     * Copies the current file.
     *
     * @return bool `true` if the file was copied successfully
     *
     * @throws Exception
     */
    public function doCopy(): bool
    {
        $originalFileStream = $this->getContentsStream();

        // Don't copy file to itself
        if ($this->targetFolder->getBackend() === $this->resourceType->getBackend() &&
            $this->targetFolder->getPath() === $this->getPath()) {
            $this->addError(Error::SOURCE_AND_TARGET_PATH_EQUAL);

            return false;
        }

        $targetFilename = $this->getTargetFilename();

        if ($this->targetFolder->containsFile($targetFilename) && !str_contains($this->copyOptions, 'overwrite')) {
            $this->addError(Error::ALREADY_EXIST);

            return false;
        }

        if ($this->targetFolder->putStream($targetFilename, $originalFileStream)) {
            $resizedImageRepository = $this->resourceType->getResizedImageRepository();
            $resizedImageRepository->copyResizedImages(
                $this->resourceType,
                $this->folder,
                $this->sourceFileName,
                $this->targetFolder->getResourceType(),
                $this->targetFolder->getClientCurrentFolder(),
                $targetFilename
            );

            $this->getCache()->copy(
                Path::combine($this->resourceType->getName(), $this->folder, $this->sourceFileName),
                Path::combine(
                    $this->targetFolder->getResourceType()->getName(),
                    $this->targetFolder->getClientCurrentFolder(),
                    $targetFilename
                )
            );

            return true;
        }
        $this->addError(Error::ACCESS_DENIED);

        return false;
    }

    /**
     * Returns the target file name of the copied file.
     */
    public function getTargetFilename(): string
    {
        if (
            $this->targetFolder->containsFile($this->getFilename()) &&
            !str_contains($this->copyOptions, 'overwrite') &&
            str_contains($this->copyOptions, 'autorename')
        ) {
            $this->autorename();
        }

        return $this->fileName;
    }

    public function getFileName(): string
    {
        return $this->sourceFileName;
    }

    /**
     * @copydoc File::autorename()
     */
    public function autorename(Backend $backend = null, string $path = ''): bool
    {
        return parent::autorename($this->targetFolder->getBackend(), $this->targetFolder->getPath());
    }

    /**
     * Returns the source file name of the copied file.
     */
    public function getSourceFilename(): string
    {
        return $this->sourceFileName;
    }

    /**
     * Returns the target path of the copied file.
     */
    public function getTargetFilePath(): string
    {
        return Path::combine($this->getTargetFolder()->getPath(), $this->getTargetFilename());
    }

    /**
     * Returns the target folder for a copied file.
     */
    public function getTargetFolder(): WorkingFolder
    {
        return $this->targetFolder;
    }

    /**
     * Returns the source file name of the copied file.
     */
    public function getSourceFilePath(): string
    {
        return Path::combine($this->getPath(), $this->sourceFileName);
    }

    /**
     * Validates the copied file.
     *
     * @return bool `true` if the copied file is valid and ready to be copied
     *
     * @throws Exception
     */
    public function isValid(): bool
    {
        if (!$this->hasValidFilename() || !$this->hasValidPath()) {
            throw new InvalidRequestException('Invalid filename or path');
        }

        if (!$this->hasAllowedExtension()) {
            $this->addError(Error::INVALID_EXTENSION);

            return false;
        }

        if ($this->isHidden() || $this->hasHiddenPath()) {
            throw new InvalidRequestException('Copied file is hidden');
        }

        if (!$this->exists()) {
            $this->addError(Error::FILE_NOT_FOUND);

            return false;
        }

        if (!$this->hasAllowedSize()) {
            $this->addError(Error::UPLOADED_TOO_BIG);

            return false;
        }

        return true;
    }

    /**
     * Checks if the file has an extension allowed in both source and target ResourceTypes.
     *
     * @return bool `true` if the file has an extension allowed in source and target directories
     */
    public function hasAllowedExtension(): bool
    {
        $extension = $this->getExtension();

        return parent::hasAllowedExtension() &&
            $this->targetFolder->getResourceType()->isAllowedExtension($extension);
    }

    /**
     * Checks if the copied file size does not exceed the file size limit set for the target folder.
     *
     * @throws FileNotFoundException
     */
    public function hasAllowedSize(): bool
    {
        $filePath = $this->getFilePath();
        $backend = $this->resourceType->getBackend();

        if (!$backend->has($filePath)) {
            return false;
        }

        $fileMetadata = $backend->getMetadata($filePath);

        $fileSize = $fileMetadata['size'];

        $maxSize = $this->targetFolder->getResourceType()->getMaxSize();

        return !($maxSize && $fileSize > $maxSize);
    }
}
