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

namespace CKSource\CKFinder\Authentication;

use Closure;

/**
 * The CallableAuthentication class.
 *
 * Basic CKFinder authentication class that authenticates the current user
 * using a PHP callable provided in the configuration file.
 */
class CallableAuthentication implements AuthenticationInterface
{
    /**
     * Constructor.
     */
    public function __construct(protected Closure $authCallable)
    {}

    /**
     * @return bool `true` if the current user was successfully authenticated within CKFinder
     */
    public function authenticate(): bool
    {
        return call_user_func($this->authCallable);
    }
}
