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

namespace CKSource\CKFinder\Event;

use CKSource\CKFinder\Filesystem\File\DeletedFile;
use JetBrains\PhpStorm\{Deprecated, Pure};

/**
 * The DeleteFileEvent event class.
 */
class DeleteFileEvent extends CKFinderEvent
{
    /**
     * Constructor.
     */
    #[Pure]
    public function __construct($app, protected DeletedFile $deletedFile)
    {
        parent::__construct($app);
    }

    /**
     * Returns the deleted file object.
     */
    #[Deprecated('please use getFile() instead', '%class%->getFile()')]
    public function getDeletedFile(): DeletedFile
    {
        return $this->deletedFile;
    }

    /**
     * Returns the deleted file object.
     */
    public function getFile(): DeletedFile
    {
        return $this->deletedFile;
    }
}
