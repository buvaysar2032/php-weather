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
    Cache\CacheManager,
    Config,
    Exception\InvalidExtensionException,
    Image,
    ResizedImage\ResizedImageRepository,
    Utils};
use Exception;
use CKSource\CKFinder\Filesystem\{File\DownloadedFile, Folder\WorkingFolder, Path};
use DateTime;
use Symfony\Component\{HttpFoundation\Request, HttpFoundation\Response};

/**
 * The ImagePreview command class.
 *
 * This command produces a resized copy of the image that
 * fits requested maximum dimensions.
 */
class ImagePreview extends CommandAbstract
{
    protected array $requires = [Permission::FILE_VIEW];

    /**
     * @throws InvalidExtensionException
     * @throws Exception
     */
    public function execute(
        Request $request,
        Config $config,
        WorkingFolder $workingFolder,
        ResizedImageRepository $resizedImageRepository,
        CacheManager $cache
    ): Response {
        $fileName = (string)$request->query->get('fileName');
        [$requestedWidth, $requestedHeight] = Image::parseSize((string)$request->get('size'));

        $downloadedFile = new DownloadedFile($fileName, $this->app);
        $downloadedFile->isValid();

        if (!Image::isSupportedExtension(
            pathinfo($fileName, PATHINFO_EXTENSION),
            $config->get('thumbnails.bmpSupported')
        )) {
            throw new InvalidExtensionException('Unsupported image type or not image file');
        }

        Utils::removeSessionCacheHeaders();

        $response = new Response();
        $response->setPublic();
        $response->setEtag(dechex($downloadedFile->getTimestamp()) . '-' . dechex($downloadedFile->getSize()));

        $lastModificationDate = new DateTime();
        $lastModificationDate->setTimestamp($downloadedFile->getTimestamp());

        $response->setLastModified($lastModificationDate);

        if ($response->isNotModified($request)) {
            return $response;
        }

        $imagePreviewCacheExpires = (int)$config->get('cache.imagePreview');

        if ($imagePreviewCacheExpires > 0) {
            $response->setMaxAge($imagePreviewCacheExpires);

            $expireTime = new DateTime();
            $expireTime->modify('+' . $imagePreviewCacheExpires . 'seconds');
            $response->setExpires($expireTime);
        }

        $cachedInfoPath = Path::combine(
            $workingFolder->getResourceType()->getName(),
            $workingFolder->getClientCurrentFolder(),
            $fileName
        );

        $cachedInfo = $cache->get($cachedInfoPath);

        $resultImage = null;

        // Try to reuse existing resized image
        if ($cachedInfo && isset($cachedInfo['width'], $cachedInfo['height'])) {
            // Fix received aspect ratio
            $size = Image::calculateAspectRatio(
                $requestedWidth,
                $requestedHeight,
                $cachedInfo['width'],
                $cachedInfo['height']
            );
            $resizedImage = $resizedImageRepository->getResizedImageBySize(
                $workingFolder->getResourceType(),
                $workingFolder->getClientCurrentFolder(),
                $fileName,
                $size['width'],
                $size['height']
            );
            if ($resizedImage) {
                $resultImage = Image::create($resizedImage->getImageData());
            }
        }

        // Fallback - get and resize the original image
        if (is_null($resultImage)) {
            $resultImage = Image::create($downloadedFile->getContents(), $config->get('thumbnails.bmpSupported'));
            $cache->set($cachedInfoPath, $resultImage->getInfo());
            $resultImage->resize($requestedWidth, $requestedHeight);
        }

        $mimeType = $resultImage->getMimeType();

        if (in_array($mimeType, ['image/bmp', 'image/x-ms-bmp'], true)) {
            $mimeType = 'image/jpeg'; // Image::getData() by default converts resized images to JPG
        }

        $response->headers->set('Content-Type', $mimeType . '; name="' . $downloadedFile->getFileName() . '"');
        $response->setContent($resultImage->getData());

        return $response;
    }
}
