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

use CKSource\CKFinder\{Acl\Permission,
    Event\CKFinderEvent,
    Event\CreateFolderEvent,
    Exception\AccessDeniedException,
    Exception\AlreadyExistsException,
    Exception\InvalidNameException};
use CKSource\CKFinder\Filesystem\Folder\WorkingFolder;
use Symfony\Component\{EventDispatcher\EventDispatcher, HttpFoundation\Request};

class CreateFolder extends CommandAbstract
{
    protected string $requestMethod = Request::METHOD_POST;

    protected array $requires = [Permission::FOLDER_CREATE];

    /**
     * @throws AccessDeniedException
     * @throws AlreadyExistsException
     * @throws InvalidNameException
     */
    public function execute(Request $request, WorkingFolder $workingFolder, EventDispatcher $dispatcher): array
    {
        $newFolderName = (string)$request->query->get('newFolderName', '');

        $createFolderEvent = new CreateFolderEvent($this->app, $workingFolder, $newFolderName);

        $dispatcher->dispatch($createFolderEvent, CKFinderEvent::CREATE_FOLDER);

        $created = false;
        $createdFolderName = null;

        if (!$createFolderEvent->isPropagationStopped()) {
            $newFolderName = $createFolderEvent->getNewFolderName();
            [$createdFolderName, $created] = $workingFolder->createDir($newFolderName);
        }

        return ['newFolder' => $createdFolderName, 'created' => (int)$created];
    }
}
