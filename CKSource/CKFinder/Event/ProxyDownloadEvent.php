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

use CKSource\CKFinder\{CKFinder, ResizedImage\ResizedImage};
use CKSource\CKFinder\Filesystem\File\DownloadedFile;
use JetBrains\PhpStorm\Pure;

/**
 * The DownloadFileEvent event class.
 */
class ProxyDownloadEvent extends CKFinderEvent
{

    /**
     * Constructor.
     *
     * @param CKFinder $app The CKFinder instance.
     */
    #[Pure]
    public function __construct(CKFinder $app, protected DownloadedFile|ResizedImage $downloadedFile)
    {
        parent::__construct($app);
    }

    /**
     * Returns the downloaded file object.
     */
    public function getFile(): ResizedImage|DownloadedFile
    {
        return $this->downloadedFile;
    }
}
