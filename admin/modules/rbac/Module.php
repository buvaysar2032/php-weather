<?php

namespace admin\modules\rbac;

use Yii;
use yii\i18n\PhpMessageSource;

/**
 * GUI manager for RBAC.
 *
 * Use [[\yii\base\Module::$controllerMap]] to change property of controller.
 *
 * ```php
 * 'controllerMap' => [
 *     'assignment' => [
 *         'class' => 'admin\modules\rbac\controllers\AssignmentController',
 *         'userIdentityClass' => 'app\models\User',
 *         'searchClass' => [
 *              'class' => 'admin\modules\rbac\models\search\AssignmentSearch',
 *              'pageSize' => 10,
 *         ],
 *         'idField' => 'id',
 *         'usernameField' => 'username'
 *         'gridViewColumns' => [
 *              'id',
 *              'username',
 *              'email'
 *         ],
 *     ],
 * ],
 * ```php
 */
class Module extends \yii\base\Module
{
    public const MODULE_MESSAGES = 'modules/rbac/';
    /**
     * The default route of this module. Defaults to 'default'
     */
    public $defaultRoute = 'assignment';

    /**
     * The namespace that controller classes are in
     */
    public $controllerNamespace = 'admin\modules\rbac\controllers';

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();
        Yii::$app->i18n->translations[self::MODULE_MESSAGES . '*'] = [
            'class' => PhpMessageSource::class,
            'basePath' => '@root/admin/modules/rbac/messages',
            'fileMap' => [
                self::MODULE_MESSAGES => 'rbac.php',
            ]
        ];
    }
}
