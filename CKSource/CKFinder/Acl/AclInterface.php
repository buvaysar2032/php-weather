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

namespace CKSource\CKFinder\Acl;

/**
 * The Acl interface.
 */
interface AclInterface
{
    /**
     * Allows a permission in the chosen folder.
     *
     * @param string $resourceType the resource type identifier (also `*` for all resource types)
     * @param string $folderPath   the folder path
     * @param int    $permission   the permission numeric value
     * @param string $role         the user role name (also `*` for all roles)
     *
     * @see Permission
     */
    public function allow(string $resourceType, string $folderPath, int $permission, string $role): Acl;

    /**
     * Disallows a permission in the chosen folder.
     *
     * @param string $resourceType the resource type identifier (also `*` for all resource types)
     * @param string $folderPath   the folder path
     * @param int    $permission   the permission numeric value
     * @param string $role         the user role name (also `*` for all roles)
     *
     * @see Permission
     */
    public function disallow(string $resourceType, string $folderPath, int $permission, string $role): Acl;

    /**
     * Checks if a role has the required permission for a folder.
     *
     * @param string      $resourceType the resource type identifier (also `*` for all resource types)
     * @param string      $folderPath   the folder path
     * @param int         $permission   the permission numeric value
     * @param string|null $role         the user role name (also `*` for all roles)
     *
     * @see Permission
     */
    public function isAllowed(string $resourceType, string $folderPath, int $permission, string $role = null): bool;

    /**
     * Computes a mask based on the current user role and ACL rules.
     *
     * @param string      $resourceType the resource type identifier (also `*` for all resource types)
     * @param string      $folderPath   the folder path
     * @param string|null $role         the user role name (also `*` for all roles)
     *
     * @see MaskBuilder
     */
    public function getComputedMask(string $resourceType, string $folderPath, string $role = null): int;
}
