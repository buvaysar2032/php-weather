<?php

namespace common\fixtures;

use common\modules\user\models\User;
use yii\test\ActiveFixture;

/**
 * Class UserFixture
 *
 * @package common\fixtures
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class UserFixture extends ActiveFixture
{
    /**
     * {@inheritdoc}
     */
    public $modelClass = User::class;
}