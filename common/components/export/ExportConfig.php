<?php

namespace common\components\export;

/**
 * Interface ExportConfig
 *
 * @package admin\widgets\export
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
interface ExportConfig
{
    public static function getColumns(): array;
}
