<?php

namespace admin\modules\rbac\controllers;

use yii\rbac\Item;
use admin\modules\rbac\base\ItemController;

/**
 * Class PermissionController
 *
 * @package admin\modules\rbac\controllers
 */
class PermissionController extends ItemController
{
    protected int $type = Item::TYPE_PERMISSION;

    protected array $labels = ['Item' => 'Permission', 'Items' => 'Permissions'];
}
