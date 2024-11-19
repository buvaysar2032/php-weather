<?php

namespace common\enums;

/**
 * Class ParamType
 *
 * @package common\enums
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
enum ParamType: string implements DictionaryInterface
{
    use DictionaryTrait;

    case Number = 'number';
    case Text = 'text';
    case File = 'file';
    case Image = 'image';
    case Color = 'color';
    case Switch = 'switch';
    case Date = 'date';
    case Datetime = 'datetime';
    case Time = 'time';

    /**
     * {@inheritdoc}
     */
    public function description(): string
    {
        return match ($this) {
            self::Text => 'Текст',
            self::Number => 'Число',
            self::File => 'Файл',
            self::Image => 'Изображение',
            self::Color => 'Цвет',
            self::Switch => 'Переключатель',
            self::Date => 'Дата',
            self::Datetime => 'Дата и время',
            self::Time => 'Время'
        };
    }

    /**
     * {@inheritdoc}
     */
    public function color(): string
    {
        return 'var(--bs-body-color)';
    }
}