<?php

namespace admin\fixtures;

use admin\models\UserAdmin;
use yii\test\ActiveFixture;

/**
 * Class UserAdminFixture
 *
 * @package admin\fixtures
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class UserAdminFixture extends ActiveFixture
{
    /**
     * {@inheritdoc}
     */
    public $modelClass = UserAdmin::class;
}
