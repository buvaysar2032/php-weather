<?php

namespace common\enums;

/**
 * Interface DictionaryInterface
 *
 * @package common\enums
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
interface DictionaryInterface
{
    /**
     * Return validator rule for dictionary fields.
     *
     * Usage:
     * ```php
     *  public function rules(): array
     *  {
     *      return [
     *          ...other rules...
     *          LogOperation::validator('operation_type'),
     *          LogStatus::validator(['status']),
     *      ];
     *  }
     * ```
     */
    public static function validator(array|string $attributes): array;

    /**
     * Returns list of possible values
     */
    public static function values(): array;

    /**
     * Returns list of possible names
     */
    public static function names(): array;

    /**
     * List of descriptions.
     *
     * @param bool $colored Если `true`, то выделит элементы их цветом
     */
    public static function descriptions(bool $colored = false): array;

    /**
     * List of indexed by value descriptions
     *
     * Use it for dropdown lists
     *
     * @param bool $colored Weather to wrap description in colored span
     */
    public static function indexedDescriptions(bool $colored = false): array;

    /**
     * Element description
     */
    public function description(): string;

    /**
     * Element description wrapped by span with color
     */
    public function coloredDescription(): string;

    /**
     * Element color
     */
    public function color(): string;
}