<?php

use admin\modules\rbac\migrations\Migration;

class m240227_084203_create_default_roles extends Migration
{
    /**
     * @throws Exception
     */
    public function safeUp(): void
    {
        $admin = $this->createRole('admin', 'Администратор');
        $allowAllPermission = $this->createPermission('/*', 'Разрешение на все маршруты');
        $this->addChild($admin, $allowAllPermission);
        $this->createRole('manager', 'Менеджер');
    }

    public function safeDown(): void
    {
        $this->removeRole('manager');
        $this->removeRole('admin');
    }
}