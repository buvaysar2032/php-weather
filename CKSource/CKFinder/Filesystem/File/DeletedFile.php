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

use CKSource\CKFinder\{Error, Exception\InvalidExtensionException, Exception\InvalidRequestException, Filesystem\Path};
use Exception;
use League\Flysystem\FileNotFoundException;

/**
 * The DeletedFile class.
 *
 * Represents the deleted file.
 */
class DeletedFile extends ExistingFile
{
    /**
     * Deletes the current file.
     *
     * @return bool `true` if the file was deleted successfully
     * @throws Exception
     */
    public function doDelete(): bool
    {
        if ($this->resourceType->getBackend()->delete($this->getFilePath())) {
            $this->deleteThumbnails();
            $this->deleteResizedImages();
            $this->getCache()->delete(
                Path::combine($this->resourceType->getName(), $this->folder, $this->getFilename())
            );

            return true;
        }
        $this->addError(Error::ACCESS_DENIED);

        return false;
    }

    /**
     * @throws InvalidExtensionException
     * @throws InvalidRequestException
     * @throws FileNotFoundException
     */
    public function isValid(): bool
    {
        if (!$this->hasValidFilename() || !$this->hasValidPath()) {
            throw new InvalidRequestException('Invalid filename or path');
        }

        if (!$this->hasAllowedExtension()) {
            throw new InvalidExtensionException();
        }

        if ($this->isHidden() || $this->hasHiddenPath()) {
            throw new InvalidRequestException('Deleted file is hidden');
        }

        if (!$this->exists()) {
            $this->addError(Error::FILE_NOT_FOUND);

            return false;
        }

        return true;
    }
}
