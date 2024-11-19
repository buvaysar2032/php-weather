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
    Exception\InvalidRequestException,
    Filesystem\Path,
    Image,
    ResizedImage\ResizedImageRepository};
use CKSource\CKFinder\Filesystem\Folder\WorkingFolder;
use Symfony\Component\HttpFoundation\Request;

class GetResizedImages extends CommandAbstract
{
    protected array $requires = [Permission::FILE_VIEW];

    /**
     * @throws InvalidRequestException
     */
    public function execute(
        Request $request,
        WorkingFolder $workingFolder,
        ResizedImageRepository $resizedImageRepository,
        Config $config,
        CacheManager $cache
    ): array {
        $fileName = (string)$request->get('fileName');
        $sizes = (string)$request->get('sizes');

        $ext = pathinfo($fileName, PATHINFO_EXTENSION);

        if (!Image::isSupportedExtension($ext)) {
            throw new InvalidRequestException('Invalid file extension');
        }

        if ($sizes) {
            $sizes = explode(',', $sizes);
            if (array_diff($sizes, array_keys($config->get('images.sizes')))) {
                throw new InvalidRequestException('Invalid size requested');
            }
        }

        $data = [];

        $cachedInfo = $cache->get(
            Path::combine(
                $workingFolder->getResourceType()->getName(),
                $workingFolder->getClientCurrentFolder(),
                $fileName
            )
        );

        if ($cachedInfo && isset($cachedInfo['width'], $cachedInfo['height'])) {
            $data['originalSize'] = sprintf('%dx%d', $cachedInfo['width'], $cachedInfo['height']);
        }

        $resizedImages = $resizedImageRepository->getResizedImagesList(
            $workingFolder->getResourceType(),
            $workingFolder->getClientCurrentFolder(),
            $fileName,
            $sizes ?: []
        );

        $data['resized'] = $resizedImages;

        return $data;
    }
}
