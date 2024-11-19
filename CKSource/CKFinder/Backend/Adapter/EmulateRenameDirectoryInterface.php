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

namespace CKSource\CKFinder\Backend\Adapter;

/**
 * The RenameDirectoryInterface interface.
 *
 * An interface implemented by adapters that do not support renaming folders, and emulate this operation.
 */
interface EmulateRenameDirectoryInterface
{
    /**
     * Changes the directory name.
     */
    public function renameDirectory(string $path, string $newPath): bool;
}
