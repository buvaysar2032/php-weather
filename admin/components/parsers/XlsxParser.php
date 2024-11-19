<?php

namespace admin\components\parsers;

use OpenSpout\Common\{Entity\Row, Exception\IOException, Exception\UnsupportedTypeException};
use OpenSpout\Reader\Exception\ReaderNotOpenedException;
use OpenSpout\Reader\SheetInterface;
use OpenSpout\Reader\XLSX\Reader;

/**
 * Class XlsxParser
 *
 * Используется для чтения данных из файлов xlsx формата
 *
 * @package admin\components\parsers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class XlsxParser implements ParserInterface
{
    use SpoutFileToArray;

    /**
     * {@inheritdoc}
     *
     * @throws IOException
     * @throws ReaderNotOpenedException
     * @throws UnsupportedTypeException
     */
    public function fileRowIterate(string $path, callable $rowCallback): void
    {
        $reader = new Reader();
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
