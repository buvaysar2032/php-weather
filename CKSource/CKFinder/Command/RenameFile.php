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

use CKSource\CKFinder\{Acl\Permission, Event\CKFinderEvent, Event\RenameFileEvent, Exception\InvalidNameException};
use CKSource\CKFinder\Filesystem\{File\RenamedFile, Folder\WorkingFolder};
use Exception;
use Symfony\Component\{EventDispatcher\EventDispatcher, HttpFoundation\Request};

class RenameFile extends CommandAbstract
{
    protected string $requestMethod = Request::METHOD_POST;

    protected array $requires = [Permission::FILE_RENAME];

    /**
     * @throws InvalidNameException
     * @throws Exception
     */
    public function execute(Request $request, WorkingFolder $workingFolder, EventDispatcher $dispatcher): array
    {
        $fileName = (string)$request->query->get('fileName');
        $newFileName = (string)$request->query->get('newFileName');

        if (empty($fileName) || empty($newFileName)) {
            throw new InvalidNameException('Invalid file name');
        }

        $renamedFile = new RenamedFile(
            $newFileName,
            $fileName,
            $workingFolder->getClientCurrentFolder(),
            $workingFolder->getResourceType(),
            $this->app
        );

        $renamed = false;

        if ($renamedFile->isValid()) {
            $renamedFileEvent = new RenameFileEvent($this->app, $renamedFile);

            $dispatcher->dispatch($renamedFileEvent, CKFinderEvent::RENAME_FILE);

            if (!$renamedFileEvent->isPropagationStopped()) {
                $renamed = $renamedFile->doRename();
            }
        }

        return [
            'name' => $fileName,
            'newName' => $renamedFile->getNewFileName(),
            'renamed' => (int)$renamed
        ];
    }
}
