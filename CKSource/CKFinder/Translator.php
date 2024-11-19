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

use JsonException;

/**
 * The Translator class.
 */
class Translator
{
    /**
     * An array with translations.
     */
    protected array $translations;

    /**
     * Translator constructor.
     *
     * @throws JsonException
     */
    public function __construct(string $langCode = null)
    {
        $locale = (string)($langCode ?? $_GET['langCode'] ?? 'en');

        $this->setLocale($locale);
    }

    /**
     * Sets locale for translations.
     *
     * @throws JsonException
     */
    protected function setLocale(?string $locale): void
    {
        if (
            is_null($locale) ||
            !preg_match('/^[a-z\-]{2,5}$/', $locale) ||
            !file_exists(__DIR__ . "/locales/$locale.json")
        ) {
            $locale = 'en';
        }

        if (empty($this->translations)) {
            $this->translations = json_decode(
                file_get_contents(__DIR__ . "/locales/$locale.json"),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        }
    }

    /**
     * Translates an error message for a given error code.
     *
     * @param int $errorNumber error number
     * @param array $replacements array of replacements to use in the translated message
     */
    public function translateErrorMessage(int $errorNumber, array $replacements = []): string
    {
        $errorMessage = '';

        if ($errorNumber) {
            if (isset($this->translations['errors'][$errorNumber])) {
                $errorMessage = $this->translations['errors'][$errorNumber];

                foreach ($replacements as $from => $to) {
                    $errorMessage = str_replace('{' . $from . '}', $to, $errorMessage);
                }
            } else {
                $errorMessage = str_replace('{number}', $errorNumber, $this->translations['errorUnknown']);
            }
        }

        return $errorMessage;
    }
}
