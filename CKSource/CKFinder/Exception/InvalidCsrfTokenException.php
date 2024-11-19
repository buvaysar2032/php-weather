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

/**
 * The "invalid CSRF token" exception class.
 *
 * Thrown when received CSRF tokens do not match.
 */
class InvalidCsrfTokenException extends CKFinderException
{
    /**
     * Constructor.
     *
     * @param string         $message    the exception message
     * @param array          $parameters the parameters passed for translation
     * @param Exception|null $previous   the previous exception
     */
    #[Pure]
    public function __construct(
        string $message = 'Invalid CSRF token.',
        array $parameters = [],
        Exception $previous = null
    ) {
        parent::__construct($message, Error::INVALID_REQUEST, $parameters, $previous);
    }
}
