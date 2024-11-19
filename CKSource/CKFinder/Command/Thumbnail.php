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
    Error,
    Exception\CKFinderException,
    Exception\FileNotFoundException,
    Exception\InvalidNameException,
    Exception\InvalidRequestException,
    Image,
    Thumbnail\ThumbnailRepository,
    Utils};
use Exception;
use CKSource\CKFinder\Filesystem\{File\File, Folder\WorkingFolder};
use DateTime;
use Symfony\Component\{HttpFoundation\Request, HttpFoundation\Response};

class Thumbnail extends CommandAbstract
{
    protected array $requires = [Permission::FILE_VIEW];

    /**
     * @throws CKFinderException
     * @throws FileNotFoundException
     * @throws InvalidNameException
     * @throws InvalidRequestException
     * @throws Exception
     */
    public function execute(
        Request $request,
        WorkingFolder $workingFolder,
        Config $config,
        ThumbnailRepository $thumbnailRepository
    ): Response {
        if (!$config->get('thumbnails.enabled')) {
            throw new CKFinderException('Thumbnails feature is disabled', Error::THUMBNAILS_DISABLED);
        }

        $fileName = (string)$request->get('fileName');

        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!Image::isSupportedExtension($ext, $thumbnailRepository->isBitmapSupportEnabled())) {
            throw new InvalidNameException('Invalid source file name');
        }

        if (empty($fileName) || !File::isValidName($fileName, $config->get('disallowUnsafeCharacters'))) {
            throw new InvalidRequestException('Invalid file name');
        }

        if (!$workingFolder->containsFile($fileName)) {
            throw new FileNotFoundException();
        }

        [$requestedWidth, $requestedHeight] = Image::parseSize((string)$request->get('size'));

        $thumbnail = $thumbnailRepository->getThumbnail(
            $workingFolder->getResourceType(),
            $workingFolder->getClientCurrentFolder(),
            $fileName,
            $requestedWidth,
            $requestedHeight
        );

        Utils::removeSessionCacheHeaders();

        $response = new Response();
        $response->setPublic();
        $response->setEtag(dechex($thumbnail->getTimestamp()) . '-' . dechex($thumbnail->getSize()));

        $lastModificationDate = new DateTime();
        $lastModificationDate->setTimestamp($thumbnail->getTimestamp());

        $response->setLastModified($lastModificationDate);

        if ($response->isNotModified($request)) {
            return $response;
        }

        $thumbnailsCacheExpires = (int)$config->get('cache.thumbnails');

        if ($thumbnailsCacheExpires > 0) {
            $response->setMaxAge($thumbnailsCacheExpires);

            $expireTime = new DateTime();
            $expireTime->modify('+' . $thumbnailsCacheExpires . 'seconds');
            $response->setExpires($expireTime);
        }

        $response->headers->set(
            'Content-Type',
            $thumbnail->getMimeType() . '; name="' . $thumbnail->getFileName() . '"'
        );
        $response->setContent($thumbnail->getImageData());

        return $response;
    }
}
