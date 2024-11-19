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

namespace CKSource\CKFinder\Exception;

use CKSource\CKFinder\Error;
use Exception;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\HttpFoundation\Response;

/**
 * The "file not found" exception class.
 *
 * Thrown when the requested file cannot be found.
 */
class FileNotFoundException extends CKFinderException
{
    /**
     * {@inheritdoc}
     */
    protected int $httpStatusCode = Response::HTTP_NOT_FOUND;

    /**
     * Constructor.
     *
     * @param string         $message    the exception message
     * @param array          $parameters the parameters passed for translation
     * @param Exception|null $previous   the previous exception
     */
    #[Pure]
    public function __construct(string $message = 'File not found', array $parameters = [], Exception $previous = null)
    {
        parent::__construct($message, Error::FILE_NOT_FOUND, $parameters, $previous);
    }
}
