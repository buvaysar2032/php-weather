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

namespace CKSource\CKFinder\Filesystem\Folder;

use CKSource\CKFinder\Filesystem\{File\File, Path};
use CKSource\CKFinder\ResourceType\ResourceType;

/**
 * The Folder class.
 *
 * Represents a folder in the file system.
 */
class Folder
{
    /**
     * Backend relative path (includes the resource type directory).
     */
    protected ?string $path;

    /**
     * @param ResourceType $resourceType resource type
     * @param string       $path         resource type relative path
     */
    public function __construct(protected ResourceType $resourceType, string $path)
    {
        $this->path = Path::combine($resourceType->getDirectory(), $path);
    }

    /**
     * Checks whether `$folderName` is a valid folder name. Returns `true` on success.
     */
    public static function isValidName(string $folderName, bool $disallowUnsafeCharacters): bool
    {
        if ($disallowUnsafeCharacters && str_contains($folderName, '.')) {
            return false;
        }

        return File::isValidName($folderName, $disallowUnsafeCharacters);
    }
}
