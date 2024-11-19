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
    Config,
    Event\CKFinderEvent,
    Event\EditFileEvent,
    Exception\AlreadyExistsException,
    Exception\CKFinderException,
    Exception\InvalidExtensionException,
    Exception\InvalidNameException,
    Exception\InvalidRequestException,
    Exception\InvalidUploadException,
    Exception\UnauthorizedException,
    Image,
    ResizedImage\ResizedImageRepository,
    Thumbnail\ThumbnailRepository,
    Utils};
use CKSource\CKFinder\Filesystem\{File\EditedImage, Folder\WorkingFolder};
use Exception;
use League\Flysystem\FileNotFoundException;
use Symfony\Component\{EventDispatcher\EventDispatcher, HttpFoundation\Request};

/**
 * The ImageEdit command class.
 *
 * This command performs basic image modifications:
 * - crop
 * - rotate
 * - resize
 */
class ImageEdit extends CommandAbstract
{
    public const OPERATION_CROP = 'crop';
    public const OPERATION_ROTATE = 'rotate';
    public const OPERATION_RESIZE = 'resize';

    protected string $requestMethod = Request::METHOD_POST;

    protected array $requires = [Permission::FILE_CREATE];

    /**
     * @throws AlreadyExistsException
     * @throws CKFinderException
     * @throws \CKSource\CKFinder\Exception\FileNotFoundException
     * @throws InvalidExtensionException
     * @throws InvalidNameException
     * @throws InvalidRequestException
     * @throws InvalidUploadException
     * @throws UnauthorizedException
     * @throws FileNotFoundException
     * @throws Exception
     */
    public function execute(
        Request $request,
        WorkingFolder $workingFolder,
        EventDispatcher $dispatcher,
        Acl $acl,
        ResizedImageRepository $resizedImageRepository,
        ThumbnailRepository $thumbnailRepository,
        Config $config
    ): array {
        $fileName = (string)$request->get('fileName');
        $newFileName = (string)$request->get('newFileName');

        $editedImage = new EditedImage($fileName, $this->app, $newFileName);

        $resourceType = $workingFolder->getResourceType();

        if (empty($newFileName)) {
            $resourceTypeName = $resourceType->getName();
            $path = $workingFolder->getClientCurrentFolder();

            if (!$acl->isAllowed($resourceTypeName, $path, Permission::FILE_DELETE)) {
                throw new UnauthorizedException(
                    sprintf('Unauthorized: no FILE_DELETE permission in %s:%s', $resourceTypeName, $path)
                );
            }
        }

        if (!Image::isSupportedExtension($editedImage->getExtension())) {
            throw new InvalidExtensionException('Unsupported image type or not image file');
        }

        $image = Image::create($editedImage->getContents());

        $actions = (array)$request->get('actions');

        if (empty($actions)) {
            throw new InvalidRequestException();
        }

        foreach ($actions as $actionInfo) {
            if (!isset($actionInfo['action'])) {
                throw new InvalidRequestException('ImageEdit: action name missing');
            }

            switch ($actionInfo['action']) {
                case self::OPERATION_CROP:
                    if (!Utils::arrayContainsKeys($actionInfo, ['x', 'y', 'width', 'height'])) {
                        throw new InvalidRequestException();
                    }
                    $x = $actionInfo['x'];
                    $y = $actionInfo['y'];
                    $width = $actionInfo['width'];
                    $height = $actionInfo['height'];
                    $image->crop($x, $y, $width, $height);

                    break;
                case self::OPERATION_ROTATE:
                    if (!isset($actionInfo['angle'])) {
                        throw new InvalidRequestException();
                    }
                    $degrees = $actionInfo['angle'];
                    $bgcolor = $actionInfo['bgcolor'] ?? 0;
                    $image->rotate($degrees, $bgcolor);

                    break;
                case self::OPERATION_RESIZE:
                    if (!Utils::arrayContainsKeys($actionInfo, ['width', 'height'])) {
                        throw new InvalidRequestException();
                    }

                    $imagesConfig = $config->get('images');

                    $width = $imagesConfig['maxWidth'] && $actionInfo['width'] > $imagesConfig['maxWidth']
                        ? $imagesConfig['maxWidth'] : $actionInfo['width'];
                    $height = $imagesConfig['maxHeight'] && $actionInfo['height'] > $imagesConfig['maxHeight']
                        ? $imagesConfig['maxHeight'] : $actionInfo['height'];
                    $image->resize((int)$width, (int)$height, $imagesConfig['quality']);

                    break;
            }
        }

        $editFileEvent = new EditFileEvent($this->app, $editedImage);

        $editedImage->setNewContents($image->getData());
        $editedImage->setNewDimensions($image->getWidth(), $image->getHeight());

        if (!$editedImage->isValid()) {
            throw new InvalidUploadException('Invalid file provided');
        }

        $dispatcher->dispatch($editFileEvent, CKFinderEvent::EDIT_IMAGE);

        $saved = false;

        if (!$editFileEvent->isPropagationStopped()) {
            $saved = $editedImage->save($editFileEvent->getNewContents());

            //Remove thumbnails and resized images in case if file is overwritten
            if (empty($newFileName) && $saved) {
                $thumbnailRepository->deleteThumbnails(
                    $resourceType,
                    $workingFolder->getClientCurrentFolder(),
                    $fileName
                );
                $resizedImageRepository->deleteResizedImages(
                    $resourceType,
                    $workingFolder->getClientCurrentFolder(),
                    $fileName
                );
            }
        }

        return [
            'saved' => (int)$saved,
            'date' => Utils::formatDate(time()),
        ];
    }
}
