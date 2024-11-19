<?php

namespace common\enums;

enum SettingType: string implements DictionaryInterface
{
    use DictionaryTrait;

    case String = 'string';
    case Number = 'number';
    case Password = 'password';
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
            self::String => 'Строка',
            self::Number => 'Число',
            self::Password => 'Пароль',
            self::Text => 'Текст',
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
