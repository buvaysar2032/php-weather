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

/**
 * The AdapterInterface interface.
 */
interface AdapterInterface
{
    /**
     * Sets the value in cache for a given key.
     *
     * @return bool `true` if successful
     */
    public function set(string $key, mixed $value): bool;

    /**
     * Returns the value for a given key from cache.
     */
    public function get(string $key): ?array;

    /**
     * Deletes the value for a given key from cache.
     *
     * @return bool `true` if successful
     */
    public function delete(string $key): bool;

    /**
     * Deletes all cache entries with a given key prefix.
     *
     * @return bool `true` if successful
     */
    public function deleteByPrefix(string $keyPrefix): bool;

    /**
     * Changes the prefix for all entries given a key prefix.
     *
     * @return bool `true` if successful
     */
    public function changePrefix(string $sourcePrefix, string $targetPrefix): bool;
}
