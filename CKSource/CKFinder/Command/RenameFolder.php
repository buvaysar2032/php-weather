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
    Event\RenameFolderEvent,
    Exception\AccessDeniedException,
    Exception\AlreadyExistsException,
    Exception\InvalidNameException,
    Exception\InvalidRequestException};
use CKSource\CKFinder\Filesystem\Folder\WorkingFolder;
use League\Flysystem\{FileExistsException, FileNotFoundException};
use Symfony\Component\{EventDispatcher\EventDispatcher, HttpFoundation\Request};

class RenameFolder extends CommandAbstract
{
    protected string $requestMethod = Request::METHOD_POST;

    protected array $requires = [Permission::FOLDER_RENAME];

    /**
     * @throws AccessDeniedException
     * @throws AlreadyExistsException
     * @throws InvalidNameException
     * @throws InvalidRequestException
     * @throws FileExistsException
     * @throws FileNotFoundException
     */
    public function execute(Request $request, WorkingFolder $workingFolder, EventDispatcher $dispatcher): array
    {
        // The root folder cannot be renamed.
        if ('/' === $workingFolder->getClientCurrentFolder()) {
            throw new InvalidRequestException('Cannot rename resource type root folder');
        }

        $newFolderName = (string)$request->query->get('newFolderName');

        $renameFolderEvent = new RenameFolderEvent($this->app, $workingFolder, $newFolderName);

        $dispatcher->dispatch($renameFolderEvent, CKFinderEvent::RENAME_FOLDER);

        if (!$renameFolderEvent->isPropagationStopped()) {
            $newFolderName = $renameFolderEvent->getNewFolderName();

            return $workingFolder->rename($newFolderName);
        }

        return ['renamed' => 0];
    }
}
