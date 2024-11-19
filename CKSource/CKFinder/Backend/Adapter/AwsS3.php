<?php

/*
 * CKFinder
 * ========
 * https://ckeditor.com/ckfinder/
 * Copyright (c) 2007-2021, CKSource - Frederico Knabben. All rights reserved.
 *
 * The software, this file and its contents are subject to the CKFinder
 * License. Please read the license.txt file before using, installing, copying,
 * modifying or distribute this file or part of its contents. The contents of
 * this file is part of the Source Code of CKFinder.
 */

namespace CKSource\CKFinder\Backend\Adapter;

use CKSource\CKFinder\{CKFinder, ContainerAwareInterface, Operation\OperationManager};
use League\Flysystem\{AwsS3v3\AwsS3Adapter, Util\MimeType};

/**
 * Custom adapter for AWS-S3.
 */
class AwsS3 extends AwsS3Adapter implements ContainerAwareInterface, EmulateRenameDirectoryInterface
{
    /**
     * The CKFinder application container.
     */
    protected CKFinder $app;

    public function setContainer(CKFinder $app): void
    {
        $this->app = $app;
    }

    /**
     * Emulates changing of directory name.
     */
    public function renameDirectory(string $path, string $newPath): bool
    {
        $sourcePath = $this->applyPathPrefix(rtrim($path, '/') . '/');

        $objectsIterator = $this->s3Client->getIterator('ListObjects', [
            'Bucket' => $this->bucket,
            'Prefix' => $sourcePath,
        ]);

        $objects = array_filter(iterator_to_array($objectsIterator), static fn($v) => isset($v['Key']));

        if (!empty($objects)) {
            /** @var OperationManager $operation */
            $operation = $this->app['operation'];

            $operation->start();

            $total = count($objects);
            $current = 0;

            foreach ($objects as $entry) {
                $this->s3Client->copyObject([
                    'Bucket' => $this->bucket,
                    'Key' => $this->replacePath($entry['Key'], $path, $newPath),
                    'CopySource' => urlencode($this->bucket . '/' . $entry['Key']),
                ]);

                if ($operation->isAborted()) {
                    // Delete target folder in case if operation was aborted
                    $targetPath = $this->applyPathPrefix(rtrim($newPath, '/') . '/');

                    $this->s3Client->deleteMatchingObjects($this->bucket, $targetPath);

                    return true;
                }

                $operation->updateStatus(['total' => $total, 'current' => ++$current]);
            }

            $this->s3Client->deleteMatchingObjects($this->bucket, $sourcePath);
        }

        return true;
    }

    /**
     * Helper method that replaces a part of the key (path).
     *
     * @param string $objectPath the bucket-relative object path
     * @param string $path       the old backend-relative path
     * @param string $newPath    the new backend-relative path
     *
     * @return string the new bucket-relative path
     */
    protected function replacePath(string $objectPath, string $path, string $newPath): string
    {
        $objectPath = $this->removePathPrefix($objectPath);
        $newPath = trim($newPath, '/') . '/';
        $path = trim($path, '/') . '/';

        return $this->applyPathPrefix($newPath . substr($objectPath, strlen($path)));
    }

    /**
     * Returns a direct link to a file stored on S3.
     */
    public function getFileUrl(string $path): string
    {
        $objectPath = $this->applyPathPrefix($path);

        return $this->s3Client->getObjectUrl($this->bucket, $objectPath);
    }

    /**
     * Returns the file MIME type.
     */
    public function getMimeType($path): bool|array|string|null
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        $mimeType = MimeType::detectByFileExtension(strtolower($ext));

        return $mimeType ? ['mimetype' => $mimeType] : parent::getMimetype($path);
    }
}
