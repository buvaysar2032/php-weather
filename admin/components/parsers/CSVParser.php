<?php

namespace admin\components\parsers;

use OpenSpout\Common\{Entity\Row, Exception\IOException, Exception\UnsupportedTypeException};
use OpenSpout\Reader\CSV\Options;
use OpenSpout\Reader\CSV\Reader;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;
use OpenSpout\Reader\SheetInterface;

/**
 * Class CSVParser
 *
 * Используется для чтения данных из файлов csv формата
 *
 * @package admin\components\parsers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class CSVParser implements ParserInterface
{
    /**
     * Ожидаемый разделитель
     */
    public string $separator = ';';

    /**
     * Ожидаемая кодировка файла
     */
    public string $encoding = 'Windows-1251';

    /**
     * {@inheritdoc}
     */
    public function fileToArray($path): array
    {
        $handle = fopen($path, 'rb+');
        $values = [];
        while (($stringArray = fgetcsv($handle, 0, $this->separator)) !== false) {
            $values[] = $stringArray;
        }
        return $values;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ReaderNotOpenedException
     * @throws IOException
     * @throws UnsupportedTypeException
     */
    public function fileRowIterate(string $path, callable $rowCallback): void
    {
        $options = new Options();
        $options->FIELD_DELIMITER = $this->separator;
        $options->ENCODING = $this->encoding;
        $reader = new Reader($options);
        $reader->open($path);

        foreach ($reader->getSheetIterator() as $sheet) {
            /** @var SheetInterface $sheet */
            foreach ($sheet->getRowIterator() as $key => $row) {
                /** @var Row $row */
                $cells = $row->getCells();
                $rowCallback($cells, $key);
            }
        }
    }
}
