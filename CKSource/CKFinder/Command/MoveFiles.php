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
    Event\MoveFileEvent,
    Exception\InvalidRequestException,
    Exception\UnauthorizedException,
    ResourceType\ResourceTypeFactory};
use CKSource\CKFinder\Filesystem\File\MovedFile;
use Exception;
use Symfony\Component\{EventDispatcher\EventDispatcher, HttpFoundation\Request};

class MoveFiles extends CommandAbstract
{
    protected string $requestMethod = Request::METHOD_POST;

    protected array $requires = [Permission::FILE_RENAME, Permission::FILE_CREATE, Permission::FILE_DELETE];

    /**
     * @throws InvalidRequestException
     * @throws UnauthorizedException
     * @throws Exception
     */
    public function execute(
        Request $request,
        ResourceTypeFactory $resourceTypeFactory,
        Acl $acl,
        EventDispatcher $dispatcher
    ): array {
        $movedFiles = $request->request->all('files');

        $moved = 0;

        $errors = [];

        // Initial validation
        foreach ($movedFiles as $arr) {
            if (!isset($arr['name'], $arr['type'], $arr['folder'])) {
                throw new InvalidRequestException('Invalid request');
            }

            if (!$acl->isAllowed($arr['type'], $arr['folder'], Permission::FILE_VIEW | Permission::FILE_DELETE)) {
                throw new UnauthorizedException('Unauthorized');
            }
        }

        foreach ($movedFiles as $arr) {
            if (empty($arr['name'])) {
                continue;
            }

            $name = $arr['name'];
            $type = $arr['type'];
            $folder = $arr['folder'];

            $resourceType = $resourceTypeFactory->getResourceType($type);

            $movedFile = new MovedFile($name, $folder, $resourceType, $this->app);

            $options = $arr['options'] ?? '';

            $movedFile->setCopyOptions($options);

            if ($movedFile->isValid()) {
                $moveFileEvent = new MoveFileEvent($this->app, $movedFile);
                $dispatcher->dispatch($moveFileEvent, CKFinderEvent::MOVE_FILE);

                if (!$moveFileEvent->isPropagationStopped() && $movedFile->doMove()) {
                    ++$moved;
                }
            }

            $errors[] = $movedFile->getErrors();
        }
        if ($errors) {
            $errors = array_merge(...$errors);
        }

        $data = ['moved' => $moved];

        if (!empty($errors)) {
            $data['error'] = [
                'number' => Error::MOVE_FAILED,
                'errors' => $errors
            ];
        }

        return $data;
    }
}
