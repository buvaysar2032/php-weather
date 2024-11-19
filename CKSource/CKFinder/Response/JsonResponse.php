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

namespace CKSource\CKFinder\Response;

use stdClass;
use Symfony\Component\HttpFoundation;
use Throwable;

/**
 * The CKFinder JSON response class.
 */
class JsonResponse extends HttpFoundation\JsonResponse
{
    protected mixed $rawData;

    public function __construct($data = null, int $status = 200, array $headers = [])
    {
        if (is_null($data)) {
            $data = new stdClass();
        }

        parent::__construct($data, $status, $headers);

        $this->rawData = $data;
    }

    public function getData()
    {
        return $this->rawData;
    }

    /**
     * @throws Throwable
     */
    public function withError($errorNumber, $errorMessage = null): static
    {
        $errorData = ['number' => $errorNumber];

        if ($errorMessage) {
            $errorData['message'] = $errorMessage;
        }

        $data = (array)$this->rawData;

        $data = ['error' => $errorData] + $data;

        $this->setData($data);

        return $this;
    }

    /**
     * @throws Throwable
     */
    public function setData($data = []): static
    {
        $this->rawData = $data;

        return parent::setData($this->rawData);
    }
}
