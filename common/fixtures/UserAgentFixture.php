<?php

namespace common\fixtures;

use common\modules\user\models\UserAgent;
use yii\test\ActiveFixture;

/**
 * Class UserAgentFixture
 *
 * @package common\fixtures
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class UserAgentFixture extends ActiveFixture
{
    /**
     * {@inheritdoc}
     */
    public $modelClass = UserAgent::class;

    /**
     * {@inheritdoc}
     */
    public $depends = [UserFixture::class];
}