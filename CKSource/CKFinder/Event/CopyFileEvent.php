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

use CKSource\CKFinder\CKFinder;
use CKSource\CKFinder\Filesystem\File\CopiedFile;
use JetBrains\PhpStorm\{Deprecated, Pure};

/**
 * The CopyFileEvent event class.
 */
class CopyFileEvent extends CKFinderEvent
{
    /**
     * Constructor.
     */
    #[Pure]
    public function __construct(CKFinder $app, protected CopiedFile $copiedFile)
    {
        parent::__construct($app);
    }

    /**
     * Returns the copied file object.
     */
    #[Deprecated('please use getFile() instead', '%class%->getFile()')]
    public function getCopiedFile(): CopiedFile
    {
        return $this->copiedFile;
    }

    /**
     * Returns the copied file object.
     */
    public function getFile(): CopiedFile
    {
        return $this->copiedFile;
    }
}
