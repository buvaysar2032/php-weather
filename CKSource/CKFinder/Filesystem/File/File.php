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

namespace CKSource\CKFinder\Filesystem\File;

use CKSource\CKFinder\{Backend\Backend, Cache\CacheManager, CKFinder, Config, Filesystem\Path};
use JetBrains\PhpStorm\Pure;

/**
 * The File class.
 *
 * Base class for processed files.
 */
abstract class File
{
    /**
     * Constant used to mark files without extension.
     */
    public const NO_EXTENSION = 'NO_EXT';

    /**
     * CKFinder configuration.
     */
    protected Config $config;

    /**
     * Constructor.
     *
     * @param string $fileName File name.
     */
    public function __construct(protected string $fileName, protected CKFinder $app)
    {
        $this->config = $app['config'];
    }

    /**
     * Secures the file name from unsafe characters.
     */
    public static function secureName(
        ?string $fileName,
        bool $disallowUnsafeCharacters = true,
        mixed $forceAscii = false
    ): string {
        $fileName = str_replace([':', '*', '?', '|', '/'], '_', $fileName);

        if ($disallowUnsafeCharacters) {
            $fileName = str_replace(';', '_', $fileName);
        }

        if ($forceAscii) {
            $fileName = static::convertToAscii($fileName);
        }

        return $fileName;
    }

    /**
     * Replace accented UTF-8 characters with unaccented ASCII-7 "equivalents".
     * The purpose of this function is to replace characters commonly found in Latin
     * alphabets with something more or less equivalent from the ASCII range. This can
     * be useful for example for converting UTF-8 to something ready for a file name.
     * After the use of this function, you would probably also pass the string
     * through `utf8_strip_non_ascii` to clean out any other non-ASCII characters.
     *
     * For a more complete implementation of transliteration, see the `utf8_to_ascii` package
     * available from the phputf8 project downloads:
     * http://prdownloads.sourceforge.net/phputf8
     *
     * @return string Accented chars replaced with ASCII equivalents
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @see    http://sourceforge.net/projects/phputf8/
     */
    public static function convertToAscii(string $str): string
    {
        static $utf8LowerAccents = null;
        static $utf8UpperAccents = null;

        if (is_null($utf8LowerAccents)) {
            $utf8LowerAccents = [
                'à' => 'a', 'ô' => 'o', 'ď' => 'd', 'ḟ' => 'f', 'ë' => 'e', 'š' => 's', 'ơ' => 'o', 'ß' => 'ss',
                'ă' => 'a', 'ř' => 'r', 'ț' => 't', 'ň' => 'n', 'ā' => 'a', 'ķ' => 'k', 'ŝ' => 's', 'ỳ' => 'y',
                'ņ' => 'n', 'ĺ' => 'l', 'ħ' => 'h', 'ṗ' => 'p', 'ó' => 'o', 'ú' => 'u', 'ě' => 'e', 'é' => 'e',
                'ç' => 'c', 'ẁ' => 'w', 'ċ' => 'c', 'õ' => 'o', 'ṡ' => 's', 'ø' => 'o', 'ģ' => 'g', 'ŧ' => 't',
                'ș' => 's', 'ė' => 'e', 'ĉ' => 'c', 'ś' => 's', 'î' => 'i', 'ű' => 'u', 'ć' => 'c', 'ę' => 'e',
                'ŵ' => 'w', 'ṫ' => 't', 'ū' => 'u', 'č' => 'c', 'ö' => 'oe', 'è' => 'e', 'ŷ' => 'y', 'ą' => 'a',
                'ł' => 'l', 'ų' => 'u', 'ů' => 'u', 'ş' => 's', 'ğ' => 'g', 'ļ' => 'l', 'ƒ' => 'f', 'ž' => 'z',
                'ẃ' => 'w', 'ḃ' => 'b', 'å' => 'a', 'ì' => 'i', 'ï' => 'i', 'ḋ' => 'd', 'ť' => 't', 'ŗ' => 'r',
                'ä' => 'ae', 'í' => 'i', 'ŕ' => 'r', 'ê' => 'e', 'ü' => 'ue', 'ò' => 'o', 'ē' => 'e', 'ñ' => 'n',
                'ń' => 'n', 'ĥ' => 'h', 'ĝ' => 'g', 'đ' => 'd', 'ĵ' => 'j', 'ÿ' => 'y', 'ũ' => 'u', 'ŭ' => 'u',
                'ư' => 'u', 'ţ' => 't', 'ý' => 'y', 'ő' => 'o', 'â' => 'a', 'ľ' => 'l', 'ẅ' => 'w', 'ż' => 'z',
                'ī' => 'i', 'ã' => 'a', 'ġ' => 'g', 'ṁ' => 'm', 'ō' => 'o', 'ĩ' => 'i', 'ù' => 'u', 'į' => 'i',
                'ź' => 'z', 'á' => 'a', 'û' => 'u', 'þ' => 'th', 'ð' => 'dh', 'æ' => 'ae', 'µ' => 'u', 'ĕ' => 'e',
            ];
        }

        if (is_null($utf8UpperAccents)) {
            $utf8UpperAccents = [
                'À' => 'A', 'Ô' => 'O', 'Ď' => 'D', 'Ḟ' => 'F', 'Ë' => 'E', 'Š' => 'S', 'Ơ' => 'O', 'Ă' => 'A',
                'Ř' => 'R', 'Ț' => 'T', 'Ň' => 'N', 'Ā' => 'A', 'Ķ' => 'K', 'Ŝ' => 'S', 'Ỳ' => 'Y', 'Ņ' => 'N',
                'Ĺ' => 'L', 'Ħ' => 'H', 'Ṗ' => 'P', 'Ó' => 'O', 'Ú' => 'U', 'Ě' => 'E', 'É' => 'E', 'Ç' => 'C',
                'Ẁ' => 'W', 'Ċ' => 'C', 'Õ' => 'O', 'Ṡ' => 'S', 'Ø' => 'O', 'Ģ' => 'G', 'Ŧ' => 'T', 'Ș' => 'S',
                'Ė' => 'E', 'Ĉ' => 'C', 'Ś' => 'S', 'Î' => 'I', 'Ű' => 'U', 'Ć' => 'C', 'Ę' => 'E', 'Ŵ' => 'W',
                'Ṫ' => 'T', 'Ū' => 'U', 'Č' => 'C', 'Ö' => 'Oe', 'È' => 'E', 'Ŷ' => 'Y', 'Ą' => 'A', 'Ł' => 'L',
                'Ų' => 'U', 'Ů' => 'U', 'Ş' => 'S', 'Ğ' => 'G', 'Ļ' => 'L', 'Ƒ' => 'F', 'Ž' => 'Z', 'Ẃ' => 'W',
                'Ḃ' => 'B', 'Å' => 'A', 'Ì' => 'I', 'Ï' => 'I', 'Ḋ' => 'D', 'Ť' => 'T', 'Ŗ' => 'R', 'Ä' => 'Ae',
                'Í' => 'I', 'Ŕ' => 'R', 'Ê' => 'E', 'Ü' => 'Ue', 'Ò' => 'O', 'Ē' => 'E', 'Ñ' => 'N', 'Ń' => 'N',
                'Ĥ' => 'H', 'Ĝ' => 'G', 'Đ' => 'D', 'Ĵ' => 'J', 'Ÿ' => 'Y', 'Ũ' => 'U', 'Ŭ' => 'U', 'Ư' => 'U',
                'Ţ' => 'T', 'Ý' => 'Y', 'Ő' => 'O', 'Â' => 'A', 'Ľ' => 'L', 'Ẅ' => 'W', 'Ż' => 'Z', 'Ī' => 'I',
                'Ã' => 'A', 'Ġ' => 'G', 'Ṁ' => 'M', 'Ō' => 'O', 'Ĩ' => 'I', 'Ù' => 'U', 'Į' => 'I', 'Ź' => 'Z',
                'Á' => 'A', 'Û' => 'U', 'Þ' => 'Th', 'Ð' => 'Dh', 'Æ' => 'Ae', 'Ĕ' => 'E',
            ];
        }

        return str_replace(
            [array_keys($utf8LowerAccents), array_keys($utf8UpperAccents)],
            [array_values($utf8LowerAccents), array_values($utf8UpperAccents)],
            $str
        );
    }

    /**
     * Validates current file name.
     *
     * @return bool `true` if the file name is valid
     */
    public function hasValidFilename(): bool
    {
        return static::isValidName($this->fileName, $this->config->get('disallowUnsafeCharacters'));
    }

    /**
     * Check whether `$fileName` is a valid file name. Returns `true` on success.
     *
     * @return bool `true` if `$fileName` is a valid file name
     */
    public static function isValidName(?string $fileName, bool $disallowUnsafeCharacters = true): bool
    {
        if (
            empty($fileName) ||
            '.' === $fileName[strlen($fileName) - 1] ||
            str_contains($fileName, '..')
        ) {
            return false;
        }

        if (preg_match(',[[:cntrl:]]|[/\\\\:*?\"<>|],', $fileName)) {
            return false;
        }

        if ($disallowUnsafeCharacters && str_contains($fileName, ';')) {
            return false;
        }

        return true;
    }

    /**
     * Returns current file name.
     */
    public function getFilename(): string
    {
        return $this->fileName;
    }

    /**
     * Returns a list of current file extensions.
     *
     * For example for a file named `file.foo.bar.baz` it will return an array containing
     * `['foo', 'bar', 'baz']`.
     *
     * @param null $newFileName the file name to check if it is different from the current file name (for example for validation of
     *                          a new file name in edited files)
     */
    public function getExtensions($newFileName = null): ?array
    {
        $fileName = $newFileName ?: $this->fileName;

        if (!str_contains($fileName, '.')) {
            return null;
        }

        $pieces = explode('.', $fileName);

        array_shift($pieces); // Remove file base name

        return array_map('strtolower', $pieces);
    }

    /**
     * Renames the current file by adding a number to the file name.
     *
     * Renaming is done by adding a number in parenthesis provided that the file name does
     * not collide with any other file existing in the target backend/path.
     * For example, if the target backend path contains a file named `foo.txt`
     * and the current file name is `foo.txt`, this method will change the current file
     * name to `foo(1).txt`.
     *
     * @param Backend|null $backend target backend
     * @param string       $path    target backend-relative path
     *
     * @return bool `true` if file was renamed
     */
    public function autorename(Backend $backend = null, string $path = ''): bool
    {
        $filePath = Path::combine($path, $this->fileName);

        if (!$backend?->has($filePath)) {
            return false;
        }

        $pieces = explode('.', $this->fileName);
        $basename = array_shift($pieces);
        $extension = implode('.', $pieces);

        $i = 0;
        while (true) {
            ++$i;
            $this->fileName = "{$basename}({$i})" . (!empty($extension) ? ".{$extension}" : '');

            $filePath = Path::combine($path, $this->fileName);

            if (!$backend?->has($filePath)) {
                break;
            }
        }

        return true;
    }

    /**
     * Checks if the current file has an image extension.
     *
     * @return bool `true` if the file name has an image extension
     */
    #[Pure]
    public function isImage(): bool
    {
        $imagesExtensions = [
            'gif', 'jpeg', 'jpg', 'png', 'psd', 'bmp', 'tiff', 'tif',
            'swc', 'iff', 'jpc', 'jp2', 'jpx', 'jb2', 'xbm', 'wbmp'
        ];

        return in_array($this->getExtension(), $imagesExtensions, true);
    }

    /**
     * Returns current file extension.
     */
    public function getExtension(): string
    {
        return strtolower(pathinfo($this->fileName, PATHINFO_EXTENSION));
    }

    public function getCache(): CacheManager
    {
        return $this->app['cache'];
    }
}
