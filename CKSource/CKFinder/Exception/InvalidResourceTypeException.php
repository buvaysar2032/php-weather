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
 * The "invalid resource type" exception class.
 *
 * Thrown when a request is trying to access an invalid resource type (e.g. one that does not exist).
 */
class InvalidResourceTypeException extends CKFinderException
{
    /**
     * Constructor.
     *
     * @param string|null    $message    the exception message
     * @param array          $parameters the parameters passed for translation
     * @param Exception|null $previous   the previous exception
     */
    #[Pure]
    public function __construct(string $message = null, array $parameters = [], Exception $previous = null)
    {
        parent::__construct($message, Error::INVALID_TYPE, $parameters, $previous);
    }
}
