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
    Event\CopyFileEvent,
    Exception\InvalidRequestException,
    Exception\UnauthorizedException,
    ResourceType\ResourceTypeFactory};
use CKSource\CKFinder\Filesystem\File\CopiedFile;
use Exception;
use Symfony\Component\{EventDispatcher\EventDispatcher, HttpFoundation\Request};

class CopyFiles extends CommandAbstract
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
        $copiedFiles = $request->request->all('files');

        $copied = 0;

        $errors = [];

        // Initial validation
        foreach ($copiedFiles as $arr) {
            if (!isset($arr['name'], $arr['type'], $arr['folder'])) {
                throw new InvalidRequestException();
            }

            if (!$acl->isAllowed($arr['type'], $arr['folder'], Permission::FILE_VIEW)) {
                throw new UnauthorizedException();
            }
        }

        foreach ($copiedFiles as $arr) {
            if (empty($arr['name'])) {
                continue;
            }

            $name = $arr['name'];
            $type = $arr['type'];
            $folder = $arr['folder'];

            $resourceType = $resourceTypeFactory->getResourceType($type);

            $copiedFile = new CopiedFile($name, $folder, $resourceType, $this->app);

            $options = $arr['options'] ?? '';

            $copiedFile->setCopyOptions($options);

            if ($copiedFile->isValid()) {
                $copyFileEvent = new CopyFileEvent($this->app, $copiedFile);
                $dispatcher->dispatch($copyFileEvent, CKFinderEvent::COPY_FILE);

                if (!$copyFileEvent->isPropagationStopped() && $copiedFile->doCopy()) {
                    ++$copied;
                }
            }

            $errors[] = $copiedFile->getErrors();
        }
        if ($errors) {
            $errors = array_merge(...$errors);
        }
        $data = ['copied' => $copied];

        if (!empty($errors)) {
            $data['error'] = [
                'number' => Error::COPY_FAILED,
                'errors' => $errors,
            ];
        }

        return $data;
    }
}
