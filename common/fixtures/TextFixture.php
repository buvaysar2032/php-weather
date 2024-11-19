<?php

namespace common\fixtures;

use common\models\Text;
use yii\test\ActiveFixture;

/**
 * Class TextFixture
 *
 * @package common\fixtures
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class TextFixture extends ActiveFixture
{
    /**
     * {@inheritdoc}
     */
    public $modelClass = Text::class;
}