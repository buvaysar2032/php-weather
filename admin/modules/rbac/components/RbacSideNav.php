<?php

namespace admin\modules\rbac\components;

use Exception;
use kartik\sidenav\SideNav;

class RbacSideNav extends SideNav
{
    use ActionFilterTrait;

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function beforeRun(): bool
    {
        self::checkNavItems($this->items);
        return parent::beforeRun();
    }
}