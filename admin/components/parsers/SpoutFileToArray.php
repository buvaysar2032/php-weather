<?php

namespace admin\components\parsers;

use OpenSpout\Common\{Entity\Row, Exception\IOException, Exception\UnsupportedTypeException};
use OpenSpout\Reader\Common\Creator\ReaderFactory;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;
use OpenSpout\Reader\SheetInterface;

/**
 * Trait SpoutFileToArray
 *
 * @package admin\components\parsers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
trait SpoutFileToArray
{
    /**
     * {@inheritdoc}
     *
     * @throws IOException
     * @throws UnsupportedTypeException
     * @throws ReaderNotOpenedException
     */
    public function fileToArray(string $path): array
    {
        $reader = ReaderFactory::createFromFile($path);
        $reader->open($path);
        $array = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            /** @var SheetInterface $sheet */
            foreach ($sheet->getRowIterator() as $row) {
                /** @var Row $row */
                $rowData = [];
                foreach ($row->getCells() as $cell) {
                    $rowData[] = $cell->getValue();
                }
                $array[] = $rowData;
            }
        }
        return $array;
    }
}
