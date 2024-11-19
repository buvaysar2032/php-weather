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

namespace CKSource\CKFinder;

use CKSource\CKFinder\ResourceType\ResourceType;
use JetBrains\PhpStorm\Pure;

class Utils
{
    /**
     * Converts shorthand `php.ini` notation into bytes, much like how the PHP source does it.
     *
     * @see http://pl.php.net/manual/en/function.ini-get.php
     */
    public static function returnBytes(string $val): int
    {
        $val = strtolower(trim($val));

        if (!$val) {
            return 0;
        }

        $bytes = ltrim($val, '+');
        if (str_starts_with($bytes, '0x')) {
            $bytes = intval($bytes, 16);
        } elseif (str_starts_with($bytes, '0')) {
            $bytes = intval($bytes, 8);
        } else {
            $bytes = (int)$bytes;
        }

        switch (substr($val, -1)) {
            case 't':
                $bytes *= 1024;
            // no break
            case 'g':
                $bytes *= 1024;
            // no break
            case 'm':
                $bytes *= 1024;
            // no break
            case 'k':
                $bytes *= 1024;
        }

        return $bytes;
    }

    /**
     * The absolute path name of the currently executing script.
     * If the script is executed with CLI, as a relative path, such as `file.php` or `../file.php`,
     * <code>$_SERVER['SCRIPT_FILENAME']</code> will contain the relative path specified by the user.
     */
    public static function getRootPath(): string
    {
        if (isset($_SERVER['SCRIPT_FILENAME'])) {
            $sRealPath = dirname($_SERVER['SCRIPT_FILENAME']);
        } else {
            /**
             * realpath — Returns canonicalized absolute pathname.
             */
            $sRealPath = realpath('.');
        }

        $sRealPath = static::trimPathTrailingSlashes($sRealPath);

        /**
         * The file name of the currently executing script, relative to the document root.
         * For instance, <code>$_SERVER['PHP_SELF']</code> in a script at the address `http://example.com/test.php/foo.bar`
         * would be `/test.php/foo.bar`.
         */
        $sSelfPath = dirname($_SERVER['PHP_SELF']);
        $sSelfPath = static::trimPathTrailingSlashes($sSelfPath);

        return static::trimPathTrailingSlashes(substr($sRealPath, 0, strlen($sRealPath) - strlen($sSelfPath)));
    }

    protected static function trimPathTrailingSlashes(string $path): string
    {
        return rtrim($path, DIRECTORY_SEPARATOR . '/\\');
    }

    /**
     * Checks if an array contains all specified keys.
     *
     * @return bool `true` if the array has all required keys, `false` otherwise
     */
    public static function arrayContainsKeys(array $array, array $keys): bool
    {
        return count(array_intersect_key(array_flip($keys), $array)) === count($keys);
    }

    /**
     * Decodes URL parts.
     */
    #[Pure]
    public static function decodeURLParts(string $str): string
    {
        return static::decodeURLComponent($str);
    }

    /**
     * Decodes the URL component.
     */
    public static function decodeURLComponent(string $str): string
    {
        return rawurldecode($str);
    }

    /**
     * Encodes URL parts.
     */
    #[Pure]
    public static function encodeURLParts(string $str): string
    {
        $revert = ['%2F' => '/'];

        return strtr(static::encodeURLComponent($str), $revert);
    }

    /**
     * Simulates the `encodeURIComponent()` function available in JavaScript.
     */
    public static function encodeURLComponent(string $str): string
    {
        $revert = ['%21' => '!', '%2A' => '*', '%27' => "'", '%28' => '(', '%29' => ')'];

        return strtr(rawurlencode($str), $revert);
    }

    /**
     * Returns a formatted date string generated for a given timestamp.
     */
    public static function formatDate(int $timestamp): string
    {
        return date('YmdHis', $timestamp);
    }

    /**
     * Returns formatted file size.
     *
     * @param int $size size in bytes
     */
    public static function formatSize(int $size): int
    {
        return ($size && $size < 1024) ? 1 : (int)round($size / 1024);
    }

    /**
     * Removes any cache headers that might be set by the session cache limiter.
     * See @link http://php.net/manual/en/function.session-cache-limiter.php.
     */
    public static function removeSessionCacheHeaders(): void
    {
        $headersToRemove = ['Expires', 'Cache-Control', 'Last-Modified', 'Pragma'];

        foreach ($headersToRemove as $header) {
            header_remove($header);
        }
    }

    /**
     * Checks if a given data chunk contains HTML-like data.
     *
     * If the `mbstring` extension is available, additionally the following encodings are checked:
     * UTF-7, UTF-16BE, UTF-16LE, UTF-32BE, UTF-32LE.
     *
     * @return bool `true` if the provided code chunk contains HTML-like data
     */
    public static function containsHtml(string $chunk): bool
    {
        if (extension_loaded('mbstring')) {
            $encodingsToCheck = ['UTF-7', 'UTF-16BE', 'UTF-16LE', 'UTF-32BE', 'UTF-32LE'];
            $supportedEncodings = mb_list_encodings();

            foreach ($encodingsToCheck as $encodingFrom) {
                if (!in_array($encodingFrom, $supportedEncodings, true)) {
                    continue;
                }

                $chunkUtf8 = mb_convert_encoding($chunk, 'UTF-8', $encodingFrom);

                if (static::containsHtmlUTF8($chunkUtf8)) {
                    return true;
                }
            }
        }

        return static::containsHtmlUTF8($chunk);
    }

    /**
     * Checks if a given data chunk contains HTML-like data (assuming that the given chunk is encoded as UTF-8).
     */
    public static function containsHtmlUTF8(string $chunk): bool
    {
        if (!$chunk) {
            return false;
        }

        $chunk = strtolower($chunk);

        $chunk = trim($chunk);

        if (preg_match('/<!DOCTYPE\\W*X?HTML/im', $chunk)) {
            return true;
        }

        $tags = ['<body', '<head', '<html', '<img', '<pre', '<script', '<table', '<title'];

        foreach ($tags as $tag) {
            if (str_contains($chunk, $tag)) {
                return true;
            }
        }

        //type = javascript
        if (preg_match('!type\s*=\s*[\'"]?\s*(?:\w*/)?(?:ecma|java)!im', $chunk)) {
            return true;
        }

        //href = javascript
        //src = javascript
        //data = javascript
        if (preg_match('!(?:href|src|data)\s*=\s*[\'"]?\s*(?:ecma|java)script:!im', $chunk)) {
            return true;
        }

        //url(javascript
        if (preg_match('!url\s*\(\s*[\'"]?\s*(?:ecma|java)script:!im', $chunk)) {
            return true;
        }

        return false;
    }

    /**
     * Replaces double extensions disallowed for the resource type.
     *
     * @return string file name with replaced double extensions
     */
    public static function replaceDisallowedExtensions(string $fileName, ResourceType $resourceType): string
    {
        $pieces = explode('.', $fileName);

        if (1 === count($pieces)) {
            return current($pieces);
        }

        $basename = array_shift($pieces);
        $lastExtension = array_pop($pieces);

        foreach ($pieces as $ext) {
            $basename .= $resourceType->isAllowedExtension($ext) ? '.' : '_';
            $basename .= $ext;
        }

        // Add the last extension to the final name.
        return $basename . '.' . $lastExtension;
    }
}
