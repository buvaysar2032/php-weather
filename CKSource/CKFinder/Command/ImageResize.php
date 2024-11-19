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
    Config,
    Exception\FileNotFoundException,
    Exception\InvalidNameException,
    Exception\InvalidRequestException,
    Image,
    ResizedImage\ResizedImageRepository};
use CKSource\CKFinder\Filesystem\{File\File, Folder\WorkingFolder};
use Exception;
use Symfony\Component\HttpFoundation\Request;

class ImageResize extends CommandAbstract
{
    protected string $requestMethod = Request::METHOD_POST;

    protected array $requires = [Permission::FILE_VIEW, Permission::IMAGE_RESIZE];

    /**
     * @throws FileNotFoundException
     * @throws InvalidNameException
     * @throws InvalidRequestException
     * @throws Exception
     */
    public function execute(
        Request $request,
        WorkingFolder $workingFolder,
        Config $config,
        ResizedImageRepository $resizedImageRepository
    ): array {
        $fileName = (string)$request->query->get('fileName');

        if (empty($fileName) || !File::isValidName($fileName, $config->get('disallowUnsafeCharacters'))) {
            throw new InvalidRequestException('Invalid file name');
        }

        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!Image::isSupportedExtension($ext)) {
            throw new InvalidNameException('Invalid source file name');
        }

        if (!$workingFolder->containsFile($fileName)) {
            throw new FileNotFoundException();
        }

        [$requestedWidth, $requestedHeight] = Image::parseSize((string)$request->query->get('size'));

        $resizedImage = $resizedImageRepository->getResizedImage(
            $workingFolder->getResourceType(),
            $workingFolder->getClientCurrentFolder(),
            $fileName,
            $requestedWidth,
            $requestedHeight
        );

        return ['url' => $resizedImage->getUrl()];
    }
}
