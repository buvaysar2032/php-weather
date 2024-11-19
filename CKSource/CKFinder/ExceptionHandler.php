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

namespace CKSource\CKFinder;

use Exception;
use CKSource\CKFinder\{Exception\CKFinderException, Response\JsonResponse};
use ErrorException;
use Psr\Log\LoggerInterface;
use Symfony\Component\{EventDispatcher\EventSubscriberInterface};
use Symfony\Component\HttpKernel\{Event\ExceptionEvent, Exception\HttpException, KernelEvents};
use Throwable;

/**
 * The exception handler class.
 *
 * All errors are resolved here and passed to the response.
 */
class ExceptionHandler implements EventSubscriberInterface
{
    /**
     * Constructor.
     *
     * @param Translator           $translator translator object
     * @param bool                 $debug      `true` if debug mode is enabled
     * @param LoggerInterface|null $logger     logger
     */
    public function __construct(
        protected Translator $translator,
        protected bool $debug = false,
        protected ?LoggerInterface $logger = null
    ) {
        if ($debug) {
            set_error_handler([$this, 'errorHandler']);
        }
    }

    /**
     * Returns all events and callbacks.
     *
     * @see <a href="http://api.symfony.com/2.5/Symfony/Component/EventDispatcher/EventSubscriberInterface.html">EventSubscriberInterface</a>
     */
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => ['onCKFinderError', -255]];
    }

    /**
     * @throws Throwable
     * @throws CKFinderException
     */
    public function onCKFinderError(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();

        $exceptionCode = $throwable->getCode() ?: Error::UNKNOWN;

        $replacements = [];

        $httpStatusCode = 200;

        if ($throwable instanceof CKFinderException) {
            $replacements = $throwable->getParameters();
            $httpStatusCode = $throwable->getHttpStatusCode();
        }

        $message =
            Error::CUSTOM_ERROR === $exceptionCode
                ? $throwable->getMessage()
                : $this->translator->translateErrorMessage($exceptionCode, $replacements);

        $response = (new JsonResponse())->withError($exceptionCode, $message);

        $event->setThrowable(new HttpException($httpStatusCode));

        $event->setResponse($response);

        if ($this->debug && $this->logger) {
            $this->logger->error($throwable);
        }

        if (filter_var(ini_get('display_errors'), FILTER_VALIDATE_BOOLEAN)) {
            throw $throwable;
        }
    }

    /**
     * Custom error handler to catch all errors in the debug mode.
     *
     * @throws Exception
     */
    public function errorHandler(int $errno, string $errstr, string $errfile, int $errline): void
    {
        $wrapperException = new ErrorException($errstr, 0, $errno, $errfile, $errline);
        $this->logger->warning($wrapperException);

        if (filter_var(ini_get('display_errors'), FILTER_VALIDATE_BOOLEAN)) {
            throw $wrapperException;
        }
    }
}
