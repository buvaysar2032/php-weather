<?php

namespace common\assets\scssConverter\storage;

use RuntimeException;

/**
 * @package common\assets\scssConverter
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class FsStorage implements Storage
{
    public function exists(string $filename): bool
    {
        return file_exists($filename);
    }

    public function get(string $filename): string
    {
        $contents = file_get_contents($filename);
        if ($contents === false) {
            throw new RuntimeException('Could not read ' . $filename);
        }
        return $contents;
    }

    public function put(string $filename, string $contents): bool
    {
        return file_put_contents($filename, $contents) !== false;
    }

    public function remove(string $filename): bool
    {
        return @unlink($filename);
    }

    public function touch(string $filename, int $mtime): bool
    {
        return touch($filename, $mtime);
    }

    public function getMtime(string $filename): int
    {
        $mtime = @filemtime($filename);
        if ($mtime === false) {
            throw new RuntimeException('Could not determine mtime for ' . $filename);
        }
        return $mtime;
    }
}
