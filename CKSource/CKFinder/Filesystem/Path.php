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

namespace CKSource\CKFinder\Filesystem;

/**
 * The Path class.
 */
class Path
{
    public const REGEX_INVALID_PATH = ',(/\.)|[[:cntrl:]]|(//)|(\\\\)|([:*?\"<>|]),';

    /**
     * Checks if a given path is valid.
     *
     * @param string $path path to be validated
     *
     * @return bool true if the path is valid
     */
    public static function isValid(string $path): bool
    {
        return !preg_match(static::REGEX_INVALID_PATH, $path);
    }

    /**
     * Normalizes the path, so it starts and ends end with a "/".
     *
     * @param string $path input path
     *
     * @return string normalized path
     */
    public static function normalize(string $path): string
    {
        if ('' === $path) {
            $path = '/';
        } elseif ('/' !== $path) {
            $path = '/' . trim($path, '/') . '/';
        }

        return $path;
    }

    /**
     * This function behaves similarly to `System.IO.Path.Combine` in C#, the only difference is that it also
     * accepts null values and treats them as an empty string.
     *
     * @param string [$arg1, $arg2, ...]
     */
    public static function combine(): ?string
    {
        $args = func_get_args();

        if (!count($args)) {
            return null;
        }

        $result = array_shift($args);

        $isDirSeparator = static fn($char) => '/' === $char || '\\' === $char;

        foreach ($args as $iValue) {
            $path1 = $result;
            $path2 = $iValue;

            if (is_null($path1)) {
                $path1 = '';
            }

            if (is_null($path2)) {
                $path2 = '';
            }

            if ('' === $path2) {
                if ('' !== $path1) {
                    $_lastCharP1 = $path1[strlen($path1) - 1];
                    if (!$isDirSeparator($_lastCharP1)) {
                        $path1 .= '/';
                    }
                }
            } else {
                $_firstCharP2 = $path2[0];
                if ('' !== $path1) {
                    if (str_starts_with($path2, $path1)) {
                        $result = $path2;

                        continue;
                    }
                    $_lastCharP1 = $path1[strlen($path1) - 1];
                    if (!$isDirSeparator($_lastCharP1) && !$isDirSeparator($_firstCharP2)) {
                        $path1 .= '/';
                    } elseif ($isDirSeparator($_lastCharP1) && $isDirSeparator($_firstCharP2)) {
                        $path2 = substr($path2, 1);
                    }
                } else {
                    $result = $path2;

                    continue;
                }
            }

            $result = $path1 . $path2;
        }

        return $result;
    }
}
