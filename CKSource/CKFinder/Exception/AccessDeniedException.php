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
 * The "access denied" exception.
 *
 * Thrown when file system permissions do not allow to perform an operation
 * such as accessing a directory or writing a file.
 */
class AccessDeniedException extends CKFinderException
{
    /**
     * {@inheritdoc}
     */
    protected int $httpStatusCode = Response::HTTP_FORBIDDEN;

    /**
     * Constructor.
     *
     * @param string         $message    the exception message
     * @param array          $parameters the parameters passed for translation
     * @param Exception|null $previous   the previous exception
     */
    #[Pure]
    public function __construct(string $message = 'Access denied', array $parameters = [], Exception $previous = null)
    {
        parent::__construct($message, Error::ACCESS_DENIED, $parameters, $previous);
    }
}
