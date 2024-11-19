<?php

namespace common\components\events;

use yii\base\ModelEvent;

/**
 * Class ModelLoadEvent
 *
 * @package common\components
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class ModelLoadEvent extends ModelEvent
{
    /**
     * Список загруженных фильтров.
     */
    public array $filters = [];
}
