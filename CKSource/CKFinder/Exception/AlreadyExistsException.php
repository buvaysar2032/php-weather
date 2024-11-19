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
 * The "already exists" exception class.
 *
 * Thrown when a file or folder already exists.
 */
class AlreadyExistsException extends CKFinderException
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
        parent::__construct($message, Error::ALREADY_EXIST, $parameters, $previous);
    }
}
