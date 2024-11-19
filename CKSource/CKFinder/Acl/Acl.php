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

use CKSource\CKFinder\Acl\User\RoleContextInterface;
use CKSource\CKFinder\Filesystem\Path;
use JetBrains\PhpStorm\Pure;

/**
 * The Acl class.
 */
class Acl implements AclInterface
{
    /**
     * @brief The list of Access Control Lists entries.
     *
     * A list of array entries in the following form:
     * <pre>[folderPath][role][resourceType] => MaskBuilder</pre>
     */
    protected array $rules = [];

    /**
     * @brief Cache for computed masks.
     *
     * This array contains computed mask results to avoid double checks
     * for the same path.
     */
    protected array $cachedResults = [];

    /**
     * Constructor.
     *
     * @param \CKSource\CKFinder\Acl\User\RoleContextInterface $roleContext The role context interface.
     *                                                                      By default, an instance of SessionRoleContext is used as a role context.
     *                                                                      You can easily add a new class that implements RoleContextInterface to
     *                                                                      better fit your application.
     */
    public function __construct(protected RoleContextInterface $roleContext)
    {
    }

    /**
     * Sets rules for Access Control Lists using configuration nodes.
     *
     * It is assumed that Acl configuration nodes used here have the following form:
     *
     * @code
     * array(
     *      'role'          => 'foo',
     *      'resourceType'  => 'Images',
     *      'folder'        => '/bar',
     *
     *      // Permissions
     *      'FOLDER_VIEW'   => true,
     *      'FOLDER_CREATE' => true,
     *      'FOLDER_RENAME' => true,
     *      'FOLDER_DELETE' => true,
     *
     *      'FILE_VIEW'     => true,
     *      'FILE_CREATE'   => true,
     *      'FILE_RENAME'   => true,
     *      'FILE_DELETE'   => true
     * )
     * @endcode
     *
     * If any permission is missing, it is inherited from the parent folder.
     *
     * @param array $aclConfigNodes Access Control Lists configuration nodes
     */
    public function setRules(array $aclConfigNodes): void
    {
        foreach ($aclConfigNodes as $node) {
            $role = $node['role'] ?? '*';

            $resourceType = $node['resourceType'] ?? '*';

            $folder = $node['folder'] ?? '/';

            $permissions = Permission::getAll();

            foreach ($permissions as $permissionName => $permissionValue) {
                if (isset($node[$permissionName])) {
                    $allow = (bool)$node[$permissionName];

                    if ($allow) {
                        $this->allow($resourceType, $folder, $permissionValue, $role);
                    } else {
                        $this->disallow($resourceType, $folder, $permissionValue, $role);
                    }
                }
            }
        }
    }

    /**
     * Allows a permission for a given role.
     */
    public function allow(string $resourceType, string $folderPath, int $permission, string $role): Acl|static
    {
        $folderPath = Path::normalize($folderPath);

        if (!isset($this->rules[$folderPath][$role][$resourceType])) {
            $this->rules[$folderPath][$role][$resourceType] = new MaskBuilder();
        }

        /** @var MaskBuilder $ruleMask */
        $ruleMask = $this->rules[$folderPath][$role][$resourceType];

        $ruleMask->allow($permission);

        return $this;
    }

    /**
     * Disallows a permission for a given role.
     */
    public function disallow(string $resourceType, string $folderPath, int $permission, string $role): Acl|static
    {
        $folderPath = Path::normalize($folderPath);

        if (!isset($this->rules[$folderPath][$role][$resourceType])) {
            $this->rules[$folderPath][$role][$resourceType] = new MaskBuilder();
        }

        /** @var MaskBuilder $ruleMask */
        $ruleMask = $this->rules[$folderPath][$role][$resourceType];

        $ruleMask->disallow($permission);

        return $this;
    }

    /**
     * Checks if a given role has a permission.
     */
    public function isAllowed(string $resourceType, string $folderPath, int $permission, string $role = null): bool
    {
        $mask = $this->getComputedMask($resourceType, $folderPath, $role);

        return ($mask & $permission) === $permission;
    }

    /**
     * Returns a computed mask.
     */
    public function getComputedMask(string $resourceType, string $folderPath, string $role = null): int
    {
        $computedMask = 0;

        $role = $role ?: $this->roleContext->getRole();

        $folderPath = trim($folderPath, '/');

        if (isset($this->cachedResults[$resourceType][$folderPath])) {
            return $this->cachedResults[$resourceType][$folderPath];
        }

        $pathParts = explode('/', $folderPath);

        $currentPath = '/';

        $pathPartsCount = count($pathParts);

        for ($i = -1; $i < $pathPartsCount; ++$i) {
            if ($i >= 0) {
                if ('' === $pathParts[$i]) {
                    continue;
                }

                if (array_key_exists($currentPath . '*/', $this->rules)) {
                    $computedMask = $this->mergePathComputedMask(
                        $computedMask,
                        $resourceType,
                        $role,
                        $currentPath . '*/'
                    );
                }

                $currentPath .= $pathParts[$i] . '/';
            }

            if (array_key_exists($currentPath, $this->rules)) {
                $computedMask = $this->mergePathComputedMask($computedMask, $resourceType, $role, $currentPath);
            }
        }

        $this->cachedResults[$resourceType][$folderPath] = $computedMask;

        return $computedMask;
    }

    /**
     * Merges permission masks to allow permission inheritance from parent folders.
     *
     * @param int|string  $currentMask  the current mask numeric value
     * @param string      $resourceType the resource type identifier
     * @param string|null $role         the user role name
     * @param string      $folderPath   the folder path
     *
     * @return int computed mask numeric value
     */
    #[Pure]
    protected function mergePathComputedMask(
        int|string $currentMask,
        string $resourceType,
        ?string $role,
        string $folderPath
    ): int {
        $folderRules = $this->rules[$folderPath];

        $possibleRules = [
            ['*', '*'],
            ['*', $resourceType],
            [$role, '*'],
            [$role, $resourceType],
        ];

        foreach ($possibleRules as $rule) {
            [$role, $resourceType] = $rule;

            if (isset($folderRules[$role][$resourceType])) {
                /** @var MaskBuilder $ruleMask */
                $ruleMask = $folderRules[$role][$resourceType];

                $currentMask = $ruleMask->mergeRules($currentMask);
            }
        }

        return $currentMask;
    }
}
