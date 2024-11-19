<?php

namespace admin\components\parsers;

/**
 * Interface ParserInterface
 *
 * @package admin\components\parsers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
interface ParserInterface
{
    /**
     * Получить массив данных из табличного файла
     *
     * @param string $path Путь к файлу
     *
     * @return array Массив данных
     */
    public function fileToArray(string $path): array;

    /**
     * Построчный вызов функции к табличному файлу
     *
     * Позволяет сильно экономить оперативную память при загрузке огромных файлов
     *
     * @param string   $path        Путь к файлу
     * @param callable $rowCallback Функция вызываемая для каждой строки файла. `function (array $cells, int $key)`
     *                              где: `$cells` - array массив ячеек в строке, `$key` - int номер строки начиная с 1
     *
     * @see \OpenSpout\Common\Entity\Cell Элемент массива ячеек
     */
    public function fileRowIterate(string $path, callable $rowCallback): void;
}
