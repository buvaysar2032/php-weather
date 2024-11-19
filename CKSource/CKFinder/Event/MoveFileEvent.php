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
use CKSource\CKFinder\Filesystem\File\MovedFile;
use JetBrains\PhpStorm\{Deprecated, Pure};

/**
 * The MoveFileEvent event class.
 */
class MoveFileEvent extends CKFinderEvent
{

    /**
     * Constructor.
     * @param CKFinder $app The CKFinder instance.
     */
    #[Pure]
    public function __construct(CKFinder $app, protected MovedFile $movedFile)
    {
        parent::__construct($app);
    }

    /**
     * Returns the moved file object.
     */
    #[Pure, Deprecated('please use getFile() instead', '%class%->getFile()')]
    public function getMovedFile(): MovedFile
    {
        return $this->movedFile;
    }

    /**
     * Returns the moved file object.
     */
    public function getFile(): MovedFile
    {
        return $this->movedFile;
    }
}
