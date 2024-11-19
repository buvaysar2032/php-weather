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

use CKSource\CKFinder\{CKFinder, Exception\UnauthorizedException};
use Symfony\Component\HttpFoundation\Request;

/**
 * The base class for all Command classes.
 */
abstract class CommandAbstract
{
    /**
     * The request method - by default GET.
     */
    protected string $requestMethod = Request::METHOD_GET;

    /**
     * An array of permissions required by the command.
     */
    protected array $requires = [];

    /**
     * Constructor.
     * @param CKFinder $app The CKFinder instance.
     */
    public function __construct(protected CKFinder $app)
    {
    }

    /**
     * Injects dependency injection container to the command scope.
     */
    public function setContainer(CKFinder $app): void
    {
        $this->app = $app;
    }

    /**
     * Checks permissions required by the command before it is executed.
     *
     * @throws \Exception if access is restricted
     */
    public function checkPermissions(): void
    {
        if (!empty($this->requires)) {
            $workingFolder = $this->app->getWorkingFolder();

            $aclMask = $workingFolder->getAclMask();

            $requiredPermissionsMask = array_sum($this->requires);

            if (($aclMask & $requiredPermissionsMask) !== $requiredPermissionsMask) {
                throw new UnauthorizedException();
            }
        }
    }

    /**
     * Returns the name of the request method required by the command.
     */
    public function getRequestMethod(): string
    {
        return $this->requestMethod;
    }

    /*
     * This method is not defined as abstract to allow for parameter injection.
     * @see CKSource\CKFinder\CommandResolver::getArguments()
     */
    // public abstract function execute();
}
