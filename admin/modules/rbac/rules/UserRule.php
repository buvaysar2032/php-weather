<?php

namespace admin\modules\rbac\rules;

use Yii;
use yii\rbac\{Item, Rule};

/**
 * Class UserRule
 *
 * @package admin\modules\rbac\rules
 */
class UserRule extends Rule
{
    /**
     * @inheritdoc
     */
    public $name = 'userRule';

    /**
     * @param int|string $user
     * @param Item       $item
     * @param array      $params
     */
    public function execute($user, $item, $params): bool
    {
        return !Yii::$app->user->isGuest;
    }
}
