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

use Exception;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\HttpFoundation\Response;

/**
 * The base CKFinder exception class.
 */
class CKFinderException extends Exception
{
    /**
     * An array of parameters passed for replacements used in translation.
     */
    protected array $parameters;

    /**
     * HTTP response status code.
     */
    protected int $httpStatusCode = Response::HTTP_BAD_REQUEST;

    /**
     * Constructor.
     *
     * @param string|null    $message    the exception message
     * @param int            $code       the exception code
     * @param array          $parameters the parameters passed for translation
     * @param Exception|null $previous   the previous exception
     */
    #[Pure]
    public function __construct(
        string $message = null,
        int $code = 0,
        array $parameters = [],
        Exception $previous = null
    ) {
        $this->parameters = $parameters;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Returns parameters used for replacements during translation.
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Returns the HTTP status code for this exception.
     *
     * @return int HTTP status code for exception
     */
    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    /**
     * Sets the HTTP status code for this exception.
     */
    public function setHttpStatusCode(int $httpStatusCode): static
    {
        $this->httpStatusCode = $httpStatusCode;

        return $this;
    }
}
