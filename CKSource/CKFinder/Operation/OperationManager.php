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

namespace CKSource\CKFinder\Operation;

use CKSource\CKFinder\{CKFinder, Filesystem\Path, Response\JsonResponse};
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\{Event\ResponseEvent, KernelEvents};

/**
 * The OperationManager class.
 *
 * A class used for tracking the progress of the time-consuming operations.
 */
class OperationManager
{
    /**
     * Time interval in seconds for operation status updates.
     */
    public const UPDATE_STATUS_INTERVAL = 2;

    /**
     * Time interval in seconds for extending the execution time of the script.
     */
    public const EXTEND_EXECUTION_INTERVAL = 20;

    /**
     * The CKFinder temporary directory path.
     */
    protected string $tempDirectory;

    /**
     * Unique identifier of started operation
     */
    protected string $startedOperationId;

    /**
     * Start time timestamp.
     */
    protected int $startTime;

    /**
     * Last status update timestamp.
     */
    protected int $lastUpdateTime;

    /**
     * Last execution time extending timestamp.
     */
    protected int $lastExtendExecutionTime;

    /**
     * Constructor.
     *
     * @param CKFinder $app The app instance.
     */
    public function __construct(protected CKFinder $app)
    {
        $this->tempDirectory = $app['config']->get('tempDirectory');
    }

    /**
     * Destructor to remove temporary files if the operation was started for the current request.
     */
    public function __destruct()
    {
        if ($this->startedOperationId) {
            $directoryPath = $this->getFilePath($this->startedOperationId, null);
            $toRemove = [
                $statusFilePath = Path::combine($directoryPath, 'status'),
                $abortFilePath = Path::combine($directoryPath, 'abort'),
            ];

            foreach ($toRemove as $filePath) {
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            if (is_dir($directoryPath)) {
                rmdir($directoryPath);
            }
        }
    }

    /**
     * Returns a path for a file located in the current operation temporary directory.
     *
     * @return string file path
     */
    protected function getFilePath(string $operationId, string $file = 'status'): string
    {
        return Path::combine($this->tempDirectory, 'ckf-operation-' . $operationId, $file);
    }

    /**
     * Starts a time-consuming operation in the current request.
     *
     * @return bool `true` if operation tracking was started
     */
    public function start(): bool
    {
        $request = $this->app->getRequest();
        $operationId = (string)$request->query->get('operationId');

        if (empty($operationId) || !$this->isValidOperationId($operationId)) {
            return false;
        }

        if (!mkdir($concurrentDirectory = $this->getFilePath($operationId, null)) && !is_dir($concurrentDirectory)) {
            return false;
        }

        $this->startedOperationId = $operationId;
        $this->startTime = time();

        ignore_user_abort();

        // Session needs to be closed to not block probing requests
        session_write_close();

        return true;
    }

    /**
     * Validates the operation ID.
     *
     * @return bool `true` if the operation ID format is valid
     */
    protected function isValidOperationId(string $operationId): bool
    {
        return (bool)preg_match('/^[a-z0-9]{16}$/', $operationId);
    }

    /**
     * Aborts an operation with a given ID.
     *
     * @return bool `true` if the operation was aborted
     */
    public function abort(string $operationId): bool
    {
        if (!$this->isValidOperationId($operationId) || !$this->operationStarted($operationId)) {
            return false;
        }

        file_put_contents($this->getFilePath($operationId, 'abort'), serialize(true));

        return true;
    }

    /**
     * Checks if a temporary directory for an operation with a given ID exists.
     *
     * @return bool `true` if the directory exists - the operation was started
     */
    protected function operationStarted(string $operationId): bool
    {
        $directoryPath = $this->getFilePath($operationId, null);

        return is_dir($directoryPath);
    }

    /**
     * Updates the status of the current operation.
     *
     * @param array $status data describing the operation status
     */
    public function updateStatus(array $status): void
    {
        if ($this->startedOperationId) {
            $currentTime = time();

            if ($currentTime - $this->lastUpdateTime >= self::UPDATE_STATUS_INTERVAL) {
                $this->extendExecutionTime($currentTime);

                $this->lastUpdateTime = $currentTime;

                file_put_contents($this->getFilePath($this->startedOperationId), serialize($status));
            }
        }
    }

    /**
     * Extends the execution time of the script.
     *
     * @param int $currentTime current timestamp
     */
    protected function extendExecutionTime(int $currentTime): void
    {
        if ($currentTime - $this->lastExtendExecutionTime >= self::EXTEND_EXECUTION_INTERVAL) {
            set_time_limit(30);

            $this->lastExtendExecutionTime = $currentTime;

            // Emit some whitespaces for Nginx + FPM configuration to avoid 504 Gateway Timeout error
            if (function_exists('fastcgi_finish_request')) {
                // Clear the buffer to remove any garbage before flushing
                Response::closeOutputBuffers(0, false);
                echo ' ';
                @ob_end_flush();
                @flush();
            }
        }
    }

    /**
     * Returns the status of the current operation.
     *
     * @return array|null operation status data
     */
    public function getStatus(string $operationId): ?array
    {
        if ($this->isValidOperationId($operationId)) {
            $filePath = $this->getFilePath($operationId);
            if (file_exists($filePath)) {
                return unserialize(file_get_contents($filePath));
            }
        }

        return null;
    }

    /**
     * Adds information about aborting to the long running request response.
     */
    public function addInfoToResponse(): void
    {
        $this->app->on(KernelEvents::RESPONSE, function (ResponseEvent $event) {
            $response = $event->getResponse();

            if ($response instanceof JsonResponse) {
                $responseData = (array)$response->getData();
                $responseData = ['aborted' => $this->isAborted()] + $responseData;
                $response->setData($responseData);
            }
        }, 512);
    }

    /**
     * Checks if the operation started in the current request was aborted.
     *
     * @return bool `true` if the operation was aborted
     */
    public function isAborted(): bool
    {
        if (!$this->startedOperationId) {
            return false;
        }

        clearstatcache();

        return $this->operationStarted($this->startedOperationId) &&
            file_exists($this->getFilePath($this->startedOperationId, 'abort'));
    }
}
