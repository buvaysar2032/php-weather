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
 * The RenameFolderEvent event class.
 */
class RenameFolderEvent extends CKFinderEvent
{
    /**
     * Constructor.
     *
     * @param CKFinder      $app           The CKFinder instance.
     * @param WorkingFolder $workingFolder Working folder where the new folder is going to be renamed.
     * @param string        $newFolderName The new folder name.
     */
    #[Pure]
    public function __construct(CKFinder $app, protected WorkingFolder $workingFolder, protected string $newFolderName)
    {
        parent::__construct($app);
    }

    /**
     * Returns the working folder where the new folder is going to be renamed.
     */
    public function getWorkingFolder(): WorkingFolder
    {
        return $this->workingFolder;
    }

    /**
     * Returns the new name of the folder.
     */
    public function getNewFolderName(): string
    {
        return $this->newFolderName;
    }

    /**
     * Sets the new name for the folder.
     */
    public function setNewFolderName(string $newFolderName): void
    {
        $this->newFolderName = $newFolderName;
    }
}
