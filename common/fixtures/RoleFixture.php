<?php

namespace common\fixtures;

use common\modules\rbac\models\Role;
use yii\test\ActiveFixture;

/**
 * Class RoleFixture
 *
 * @package common\fixtures
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class RoleFixture extends ActiveFixture
{
    /**
     * {@inheritdoc}
     */
    public $modelClass = Role::class;
}