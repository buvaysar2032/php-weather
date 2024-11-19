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

use CKSource\CKFinder\{Acl\Acl,
    Acl\Permission,
    Error,
    Event\CKFinderEvent,
    Event\DeleteFileEvent,
    Exception\CKFinderException,
    Exception\InvalidExtensionException,
    Exception\InvalidRequestException,
    Exception\UnauthorizedException,
    ResourceType\ResourceTypeFactory};
use CKSource\CKFinder\Filesystem\File\DeletedFile;
use Exception;
use League\Flysystem\FileNotFoundException;
use Symfony\Component\{EventDispatcher\EventDispatcher, HttpFoundation\Request};

class DeleteFiles extends CommandAbstract
{
    protected string $requestMethod = Request::METHOD_POST;

    protected array $requires = [Permission::FILE_DELETE];

    /**
     * @throws CKFinderException
     * @throws InvalidExtensionException
     * @throws InvalidRequestException
     * @throws UnauthorizedException
     * @throws FileNotFoundException
     * @throws Exception
     */
    public function execute(
        Request $request,
        ResourceTypeFactory $resourceTypeFactory,
        Acl $acl,
        EventDispatcher $dispatcher
    ): array {
        $deletedFiles = $request->request->all('files');

        $deleted = 0;

        $errors = [];

        // Initial validation
        foreach ($deletedFiles as $arr) {
            if (!isset($arr['name'], $arr['type'], $arr['folder'])) {
                throw new InvalidRequestException('Invalid request');
            }

            if (!$acl->isAllowed($arr['type'], $arr['folder'], Permission::FILE_DELETE)) {
                throw new UnauthorizedException();
            }
        }

        foreach ($deletedFiles as $arr) {
            if (empty($arr['name'])) {
                continue;
            }

            $name = $arr['name'];
            $type = $arr['type'];
            $folder = $arr['folder'];

            $resourceType = $resourceTypeFactory->getResourceType($type);

            $deletedFile = new DeletedFile($name, $folder, $resourceType, $this->app);

            if ($deletedFile->isValid()) {
                $deleteFileEvent = new DeleteFileEvent($this->app, $deletedFile);
                $dispatcher->dispatch($deleteFileEvent, CKFinderEvent::DELETE_FILE);

                if (!$deleteFileEvent->isPropagationStopped() && $deletedFile->doDelete()) {
                    ++$deleted;
                }
            }

            $errors[] = $deletedFile->getErrors();
        }
        if ($errors) {
            $errors = array_merge(...$errors);
        }

        $data = ['deleted' => $deleted];

        if (!empty($errors)) {
            $data['error'] = [
                'number' => Error::DELETE_FAILED,
                'errors' => $errors,
            ];
        }

        return $data;
    }
}
