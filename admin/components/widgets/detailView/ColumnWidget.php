<?php

namespace admin\components\widgets\detailView;

use admin\components\widgets\ArrayWidget;
use yii\base\InvalidConfigException;

/**
 * Class DetailsViewColumnWidget
 *
 * @package admin\components\widgets\detailView
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
abstract class ColumnWidget extends ArrayWidget
{
    /**
     * Название атрибута модели
     */
    public string $attr;

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        if (empty($this->attr)) {
            throw new InvalidConfigException('`attr` is not defined');
        }
        parent::init();
    }
}
