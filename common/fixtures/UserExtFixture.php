<?php

namespace common\fixtures;

use common\modules\user\models\UserExt;
use yii\test\ActiveFixture;

/**
 * Class UserExtFixture
 *
 * @package common\fixtures
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class UserExtFixture extends ActiveFixture
{
    /**
     * {@inheritdoc}
     */
    public $modelClass = UserExt::class;

    /**
     * {@inheritdoc}
     */
    public $depends = [UserFixture::class];
}