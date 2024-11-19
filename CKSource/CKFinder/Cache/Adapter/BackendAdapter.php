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

namespace CKSource\CKFinder\Cache\Adapter;

use CKSource\CKFinder\{Backend\Backend, Filesystem\Path};
use League\Flysystem\{FileExistsException, FileNotFoundException};

/**
 * The BackendAdapter class.
 */
class BackendAdapter implements AdapterInterface
{
    /**
     * Constructor.
     */
    public function __construct(protected Backend $backend, protected ?string $cachePath = null)
    {
    }

    /**
     * Sets the value in cache under given key.
     *
     * @return bool true if successful
     */
    public function set(string $key, mixed $value): bool
    {
        return $this->backend->put($this->createCachePath($key), serialize($value));
    }

    /**
     * Creates backend-relative path for cache file for given key.
     */
    public function createCachePath(string $key, bool $prefix = false): string
    {
        return Path::combine($this->cachePath, trim($key, '/') . ($prefix ? '' : '.cache'));
    }

    /**
     * Returns value under given key from cache.
     *
     * @throws FileNotFoundException
     */
    public function get(string $key): ?array
    {
        $path = $this->createCachePath($key);

        if (!$this->backend->has($path)) {
            return null;
        }

        return unserialize($this->backend->read($path));
    }

    /**
     * Deletes value under given key  from cache.
     *
     * @return bool true if successful
     * @throws FileNotFoundException
     */
    public function delete(string $key): bool
    {
        $path = $this->createCachePath($key);

        if (!$this->backend->has($path)) {
            return false;
        }

        $this->backend->delete($path);

        $dirs = explode('/', dirname($path));

        do {
            $dirPath = implode('/', $dirs);
            $contents = $this->backend->listContents($dirPath);

            if (!empty($contents)) {
                break;
            }

            $this->backend->deleteDir($dirPath);
            array_pop($dirs);
        } while (!empty($dirs));
        return true;
    }

    /**
     * Deletes all cache entries with given key prefix.
     *
     * @return bool true if successful
     *
     * @throws FileNotFoundException
     */
    public function deleteByPrefix(string $keyPrefix): bool
    {
        $path = $this->createCachePath($keyPrefix, true);
        if ($this->backend->hasDirectory($path)) {
            return $this->backend->deleteDir($path);
        }

        return false;
    }

    /**
     * Changes prefix for all entries given key prefix.
     *
     * @return bool true if successful
     *
     * @throws FileExistsException
     * @throws FileNotFoundException
     */
    public function changePrefix(string $sourcePrefix, string $targetPrefix): bool
    {
        $sourceCachePath = $this->createCachePath($sourcePrefix, true);

        if (!$this->backend->hasDirectory($sourceCachePath)) {
            return false;
        }

        $targetCachePath = $this->createCachePath($targetPrefix, true);

        return $this->backend->rename($sourceCachePath, $targetCachePath);
    }
}
