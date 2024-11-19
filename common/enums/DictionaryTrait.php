<?php

namespace common\enums;

use yii\bootstrap5\Html;

/**
 * Trait YiiEnum
 *
 * @package common\enums
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
trait DictionaryTrait
{
    public static function validator(array|string $attributes, bool $strict = false): array
    {
        return [$attributes, EnumValidator::class, 'enum' => self::class, 'strict' => $strict];
    }

    public static function values(): array
    {
        $ranges = [];
        foreach (self::cases() as $case) {
            $ranges[] = $case->value;
        }
        return $ranges;
    }

    public static function names(): array
    {
        $names = [];
        foreach (self::cases() as $case) {
            $names[] = $case->name;
        }
        return $names;
    }


    public static function descriptions(bool $colored = false): array
    {
        $descriptions = [];
        foreach (self::cases() as $case) {
            $descriptions[] = $colored
                ? self::_wrap($case->description(), $case->color())
                : $case->description();
        }
        return $descriptions;
    }

    public static function indexedDescriptions(bool $colored = false): array
    {
        return array_combine(self::values(), self::descriptions($colored));
    }

    /**
     * Обернуть html тег с цветом, указанным в словаре - color
     *
     * @param string $txt Текст
     * @param string|null $color Цвет текста
     */
    private static function _wrap(string $txt, string $color = null): string
    {
        $options = ['style' => ['font-weight' => 'bold']];
        if ($color) {
            Html::addCssStyle($options, ['color' => $color]);
        }
        return Html::tag('span', $txt, $options);
    }

    public function coloredDescription(): string
    {
        return self::_wrap($this->description(), $this->color());
    }
}
