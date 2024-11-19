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
    Event\CKFinderEvent,
    Event\ProxyDownloadEvent,
    Exception\AccessDeniedException,
    Exception\CKFinderException,
    Exception\FileNotFoundException,
    Exception\InvalidExtensionException,
    Exception\InvalidRequestException,
    Utils};
use CKSource\CKFinder\Filesystem\{File\DownloadedFile, File\File, Folder\WorkingFolder};
use DateTime;
use Exception;
use Symfony\Component\{EventDispatcher\EventDispatcher, HttpFoundation\Request, HttpFoundation\StreamedResponse};

class Proxy extends CommandAbstract
{
    protected array $requires = [Permission::FILE_VIEW];

    /**
     * @throws AccessDeniedException
     * @throws CKFinderException
     * @throws FileNotFoundException
     * @throws InvalidExtensionException
     * @throws InvalidRequestException
     * @throws Exception
     */
    public function execute(
        Request $request,
        WorkingFolder $workingFolder,
        EventDispatcher $dispatcher,
        Config $config
    ): StreamedResponse {
        $fileName = (string)$request->query->get('fileName');
        $thumbnailFileName = (string)$request->query->get('thumbnail');

        if (!File::isValidName($fileName, $config->get('disallowUnsafeCharacters'))) {
            throw new InvalidRequestException(sprintf('Invalid file name: %s', $fileName));
        }

        $cacheLifetime = (int)$request->query->get('cache');

        if (!$workingFolder->containsFile($fileName)) {
            throw new FileNotFoundException();
        }

        if ($thumbnailFileName) {
            if (!File::isValidName($thumbnailFileName, $config->get('disallowUnsafeCharacters'))) {
                throw new InvalidRequestException(sprintf('Invalid resized image file name: %s', $fileName));
            }

            if (!$workingFolder->getResourceType()->isAllowedExtension(
                pathinfo($thumbnailFileName, PATHINFO_EXTENSION)
            )) {
                throw new InvalidExtensionException();
            }

            $resizedImageRespository = $this->app->getResizedImageRepository();
            $file = $resizedImageRespository->getExistingResizedImage(
                $workingFolder->getResourceType(),
                $workingFolder->getClientCurrentFolder(),
                $fileName,
                $thumbnailFileName
            );
            $dataStream = $file->readStream();
        } else {
            $file = new DownloadedFile($fileName, $this->app);
            $file->isValid();
            $dataStream = $workingFolder->readStream($file->getFilename());
        }

        $proxyDownload = new ProxyDownloadEvent($this->app, $file);

        $dispatcher->dispatch($proxyDownload, CKFinderEvent::PROXY_DOWNLOAD);

        if ($proxyDownload->isPropagationStopped()) {
            throw new AccessDeniedException();
        }

        $response = new StreamedResponse();
        $headers = $response->headers;
        $headers->set('Content-Type', $file->getMimeType());
        $headers->set('Content-Length', $file->getSize());
        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('Content-Disposition', 'inline; filename="' . $fileName . '"');

        if ($cacheLifetime > 0) {
            Utils::removeSessionCacheHeaders();

            $response->setPublic();
            $response->setEtag(dechex($file->getTimestamp()) . '-' . dechex($file->getSize()));

            $lastModificationDate = new DateTime();
            $lastModificationDate->setTimestamp($file->getTimestamp());

            $response->setLastModified($lastModificationDate);

            if ($response->isNotModified($request)) {
                return $response;
            }

            $response->setMaxAge($cacheLifetime);

            $expireTime = new DateTime();
            $expireTime->modify('+' . $cacheLifetime . 'seconds');
            $response->setExpires($expireTime);
        }

        $chunkSize = 1024 * 100;

        $response->setCallback(function () use ($dataStream, $chunkSize) {
            if (false === $dataStream) {
                return false;
            }
            while (!feof($dataStream)) {
                echo fread($dataStream, $chunkSize);
                flush();
                @set_time_limit(8);
            }

            return true;
        });

        return $response;
    }
}
