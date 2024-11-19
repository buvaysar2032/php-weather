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

use CKSource\CKFinder\Exception\{AlreadyExistsException,
    FileNotFoundException,
    InvalidExtensionException,
    InvalidNameException,
    InvalidRequestException,
    InvalidUploadException};

/**
 * The EditedImage class.
 *
 * Represents an image file that is edited.
 */
class EditedImage extends EditedFile
{
    protected int $newWidth;

    protected int $newHeight;

    /**
     * Sets new image dimensions.
     */
    public function setNewDimensions(int $newWidth, int $newHeight): void
    {
        $this->newWidth = $newWidth;
        $this->newHeight = $newHeight;
    }

    /**
     * @copydoc EditedFile::isValid()
     *
     * @throws AlreadyExistsException
     * @throws FileNotFoundException
     * @throws InvalidExtensionException
     * @throws InvalidNameException
     * @throws InvalidRequestException
     * @throws InvalidUploadException
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function isValid(): bool
    {
        $imagesConfig = $this->config->get('images');

        if (($imagesConfig['maxWidth'] && $this->newWidth > $imagesConfig['maxWidth']) ||
            ($imagesConfig['maxHeight'] && $this->newHeight > $imagesConfig['maxHeight'])) {
            throw new InvalidUploadException('The image dimensions exceeds images.maxWidth or images.maxHeight');
        }

        return parent::isValid();
    }
}
