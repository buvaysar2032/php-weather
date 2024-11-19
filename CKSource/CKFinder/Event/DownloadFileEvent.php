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
use CKSource\CKFinder\Filesystem\File\DownloadedFile;
use JetBrains\PhpStorm\{Deprecated, Pure};

/**
 * The DownloadFileEvent event class.
 */
class DownloadFileEvent extends CKFinderEvent
{
    /**
     * Constructor.
     * @param CKFinder $app The CKFinder instance.
     */
    #[Pure]
    public function __construct(CKFinder $app, protected DownloadedFile $downloadedFile)
    {
        parent::__construct($app);
    }

    /**
     * Returns the downloaded file object.
     */
    #[Deprecated('please use getFile() instead', '%class%->getFile()')]
    public function getDownloadedFile(): DownloadedFile
    {
        return $this->downloadedFile;
    }

    /**
     * Returns the downloaded file object.
     */
    public function getFile(): DownloadedFile
    {
        return $this->downloadedFile;
    }
}
