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

namespace CKSource\CKFinder\Acl\User;

/**
 * The SessionRoleContext class.
 *
 * SessionRoleContext is used to get the user role from the defined $_SESSION field.
 */
class SessionRoleContext implements RoleContextInterface
{
    /**
     * Sets the $_SESSION field name to use.
     *
     * @param string $sessionRoleField The $_SESSION field name to use.
     */
    public function __construct(protected string $sessionRoleField)
    {
    }

    /**
     * Returns the role name of the current user.
     */
    public function getRole(): ?string
    {
        if ('' !== $this->sessionRoleField && isset($_SESSION[$this->sessionRoleField])) {
            return (string)$_SESSION[$this->sessionRoleField];
        }

        return null;
    }
}
