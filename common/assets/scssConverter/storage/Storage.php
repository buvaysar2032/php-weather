<?php

namespace common\assets\scssConverter\storage;

/**
 * @package common\assets\scssConverter
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
interface Storage
{
    public function exists(string $filename): bool;
    public function get(string $filename): string;
    public function put(string $filename, string $contents): bool;
    public function remove(string $filename): bool;
    public function touch(string $filename, int $mtime): bool;
    public function getMtime(string $filename): int;
}
