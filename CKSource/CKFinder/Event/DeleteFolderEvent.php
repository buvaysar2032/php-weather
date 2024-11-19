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
use CKSource\CKFinder\Filesystem\Folder\WorkingFolder;
use JetBrains\PhpStorm\Pure;

/**
 * The DeleteFolderEvent event class.
 */
class DeleteFolderEvent extends CKFinderEvent
{
    /**
     * Constructor.
     * @param CKFinder      $app           The CKFinder instance.
     * @param WorkingFolder $workingFolder The working folder that is going to be deleted.
     */
    #[Pure]
    public function __construct(CKFinder $app, protected WorkingFolder $workingFolder)
    {
        parent::__construct($app);
    }

    /**
     * Returns the working folder that is going to be deleted.
     */
    public function getWorkingFolder(): WorkingFolder
    {
        return $this->workingFolder;
    }
}
