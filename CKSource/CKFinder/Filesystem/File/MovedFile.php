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

use Exception;
use CKSource\CKFinder\{Filesystem\Path};

/**
 * The MovedFile class.
 *
 * Represents the moved file.
 */
class MovedFile extends CopiedFile
{
    /**
     * Moves the current file.
     *
     * @return bool `true` if the file was moved successfully
     *
     * @throws Exception
     */
    public function doMove(): bool
    {
        $originalFilePath = $this->getFilePath();
        $originalFileName = $this->getFilename(); // Save original file name - it may be autorenamed when copied

        if ($this->doCopy()) {
            // Remove source file
            $this->deleteThumbnails();
            $this->resourceType->getResizedImageRepository()->deleteResizedImages(
                $this->resourceType,
                $this->folder,
                $originalFileName
            );
            $this->getCache()->delete(Path::combine($this->resourceType->getName(), $this->folder, $originalFileName));

            return $this->resourceType->getBackend()->delete($originalFilePath);
        }

        return false;
    }
}
