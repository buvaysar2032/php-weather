<?php

namespace admin\modules\rbac\controllers;

use yii\rbac\Item;
use admin\modules\rbac\base\ItemController;

/**
 * Class RoleController
 *
 * @package admin\modules\rbac\controllers
 */
class RoleController extends ItemController
{
    protected int $type = Item::TYPE_ROLE;

    protected array $labels = ['Item' => 'Role', 'Items' => 'Roles'];
}
