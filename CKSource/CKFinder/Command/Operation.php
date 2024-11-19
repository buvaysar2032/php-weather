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

namespace CKSource\CKFinder\Command;

use CKSource\CKFinder\Operation\OperationManager;
use Symfony\Component\HttpFoundation\Request;

class Operation extends CommandAbstract
{
    public function execute(Request $request): ?array
    {
        $operationId = (string)$request->query->get('operationId');

        /** @var OperationManager $operation */
        $operation = $this->app['operation'];

        if ($request->query->get('abort')) {
            $operation->abort($operationId);
        }

        return $operation->getStatus($operationId);
    }
}
