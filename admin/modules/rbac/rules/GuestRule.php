<?php

namespace admin\modules\rbac\rules;

use Yii;
use yii\rbac\{Item, Rule};

/**
 * Class GuestRule
 *
 * @package admin\modules\rbac\rules
 */
class GuestRule extends Rule
{
    /**
     * @inheritdoc
     */
    public $name = 'guestRule';

    /**
     * @param int|string $user
     * @param Item       $item
     * @param array      $params
     */
    public function execute($user, $item, $params): bool
    {
        return Yii::$app->user->isGuest;
    }
}
