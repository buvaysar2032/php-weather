<?php

namespace common\fixtures;

use common\modules\user\models\Email;
use yii\test\ActiveFixture;

/**
 * Class EmailFixture
 *
 * @package common\fixtures
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class EmailFixture extends ActiveFixture
{
    /**
     * {@inheritdoc}
     */
    public $modelClass = Email::class;

    /**
     * {@inheritdoc}
     */
    public $depends = [UserFixture::class];
}