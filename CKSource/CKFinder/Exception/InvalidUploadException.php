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
 * The "invalid upload" exception class.
 *
 * Thrown when an invalid file upload request was received.
 */
class InvalidUploadException extends CKFinderException
{
    /**
     * Constructor.
     *
     * @param string         $message    the exception message
     * @param int            $code       the exception code
     * @param array          $parameters the parameters passed for translation
     * @param Exception|null $previous   the previous exception
     */
    #[Pure]
    public function __construct(
        string $message = 'Invalid upload',
        int $code = Error::UPLOADED_INVALID,
        array $parameters = [],
        Exception $previous = null
    ) {
        parent::__construct($message, $code, $parameters, $previous);
    }
}
