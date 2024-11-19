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

namespace CKSource\CKFinder\Command;

use CKSource\CKFinder\Acl\Permission;
use CKSource\CKFinder\Filesystem\{Folder\WorkingFolder, Path};
use stdClass;

class GetFolders extends CommandAbstract
{
    protected array $requires = [Permission::FOLDER_VIEW];

    public function execute(WorkingFolder $workingFolder): stdClass
    {
        $directories = $workingFolder->listDirectories();

        $data = new stdClass();
        $data->folders = [];

        $backend = $workingFolder->getBackend();

        $resourceType = $workingFolder->getResourceType();

        foreach ($directories as $directory) {
            $data->folders[] = [
                'name' => $directory['basename'],
                'hasChildren' => $backend->containsDirectories(
                    $resourceType,
                    Path::combine($workingFolder->getClientCurrentFolder(), $directory['basename'])
                ),
                'acl' => $directory['acl']
            ];
        }

        // Sort folders
        usort($data->folders, static function ($a, $b) {
            return strnatcasecmp($a['name'], $b['name']);
        });

        return $data;
    }
}
