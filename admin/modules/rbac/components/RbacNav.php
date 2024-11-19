<?php

namespace admin\modules\rbac\components;

use Exception;
use kartik\nav\NavX;

class RbacNav extends NavX
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