<?php

use admin\modules\rbac\Module;

/**
 * @var $this yii\web\View
 */
$this->params['sidebarHeader'] = Yii::t(Module::MODULE_MESSAGES, 'RBAC');
$this->params['sidebar'] = [
    [
        'label' => Yii::t(Module::MODULE_MESSAGES, 'Assignments'),
        'url' => ['assignment/index']
    ],
    [
        'label' => Yii::t(Module::MODULE_MESSAGES, 'Roles'),
        'url' => ['role/index']
    ],
    [
        'label' => Yii::t(Module::MODULE_MESSAGES, 'Permissions'),
        'url' => ['permission/index']
    ],
    [
        'label' => Yii::t(Module::MODULE_MESSAGES, 'Routes'),
        'url' => ['route/index']
    ],
    [
        'label' => Yii::t(Module::MODULE_MESSAGES, 'Rules'),
        'url' => ['rule/index']
    ]
];
