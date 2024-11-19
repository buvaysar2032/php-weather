<?php

/*
 * CKFinder
 * ========
 * https://ckeditor.com/ckeditor-4/ckfinder/
 * Copyright (c) 2007-2023, CKSource Holding sp. z o.o. All rights reserved.
 *
 * The software, this file and its contents are subject to the CKFinder
 * License. Please read the license.txt file before using, installing, copying,
 * modifying or distribute this file or part of its contents. The contents of
 * this file is part of the Source Code of CKFinder.
 */

namespace CKSource\CKFinder\Command;

use CKSource\CKFinder\{Cache\CacheManager,
    CKFinder,
    Config,
    Exception\FileNotFoundException,
    Exception\InvalidExtensionException,
    Exception\InvalidNameException,
    Exception\InvalidRequestException,
    Exception\InvalidUploadException,
    Response\JsonResponse,
    Thumbnail\ThumbnailRepository};
use CKSource\CKFinder\Filesystem\Folder\WorkingFolder;
use Symfony\Component\{EventDispatcher\EventDispatcher,
    HttpFoundation\Request,
    HttpFoundation\Response,
    HttpKernel\KernelEvents};
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class QuickUpload extends FileUpload
{
    /**
     * @param CKFinder $app The CKFinder instance.
     */
    public function __construct(protected CKFinder $app)
    {
        parent::__construct($app);

        $app->on(KernelEvents::RESPONSE, [$this, 'onQuickUploadResponse']);
    }

    /**
     * @throws FileNotFoundException
     * @throws InvalidExtensionException
     * @throws InvalidNameException
     * @throws InvalidRequestException
     * @throws InvalidUploadException
     */
    public function execute(
        Request $request,
        WorkingFolder $workingFolder,
        EventDispatcher $dispatcher,
        Config $config,
        CacheManager $cache,
        ThumbnailRepository $thumbsRepository
    ): array {
        // Don't add info about current folder to this command response
        $workingFolder->omitResponseInfo();

        $responseData = parent::execute($request, $workingFolder, $dispatcher, $config, $cache, $thumbsRepository);

        // Get url to a file
        if (isset($responseData['fileName'])) {
            $responseData['url'] = $workingFolder->getFileUrl($responseData['fileName']);
        }

        return $responseData;
    }

    /**
     * @throws \JsonException
     */
    public function onQuickUploadResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();

        if ($request->get('responseType') === 'json') {
            return;
        }

        $response = $event->getResponse();

        $funcNum = (string)$request->get('CKEditorFuncNum');
        $funcNum = preg_replace('/[\D]/', '', $funcNum);

        if ($response instanceof JsonResponse) {
            $responseData = $response->getData();

            $fileUrl = $responseData['url'] ?? '';
            $errorMessage = $responseData['error']['message'] ?? '';

            ob_start();
            ?>
          <script type="text/javascript">
            window.parent.CKEDITOR.tools.callFunction(
                <?= json_encode($funcNum, JSON_THROW_ON_ERROR) ?>,
                <?= json_encode($fileUrl, JSON_THROW_ON_ERROR) ?>,
                <?= json_encode($errorMessage, JSON_THROW_ON_ERROR) ?>
            )
          </script>
            <?php
            $event->setResponse(new Response(ob_get_clean()));
        }
    }
}
