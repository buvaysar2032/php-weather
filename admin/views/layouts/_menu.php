<?php

use admin\components\widgets\searchMenu\SearchMenu;
use admin\models\UserAdminSearch;
use admin\modules\modelExportImport\models\ModelImportLogSearch;
use admin\modules\rbac\components\RbacNav;
use common\components\helpers\UserUrl;
use common\models\{ExportListSearch, TextSearch};
use common\modules\log\Log;
use common\modules\mail\models\{MailingLogSearch, MailingSearch, MailTemplateSearch};
use common\modules\notification\widgets\NotificationBell;
use common\modules\user\models\UserSearch;
use kartik\icons\Icon;
use yii\bootstrap5\{Html, NavBar};
use yii\web\View;

/**
 * @var $this View
 */

NavBar::begin([
    'brandLabel' => Yii::$app->name,
    'brandUrl' => Yii::$app->homeUrl,
    'options' => [
        'class' => [
            'navbar',
            Yii::$app->themeManager->isDark ? 'navbar-dark' : 'navbar-light',
            Yii::$app->themeManager->isDark ? 'bg-dark' : 'bg-light',
            'blue-grey',
            'tint-color-5',
            'navbar-fixed-top',
            'navbar-expand-lg'
        ]
    ]
]);
echo SearchMenu::widget();
$menuItems = [];
if (!Yii::$app->user->isGuest) {
    /** @var Log $logModule */
    $logModule = Yii::$app->getModule('log');
    $menuItems = [
        ['label' => Icon::show('chart-bar') . 'Статистика', 'url' => ['/statistic/index']],
        [
            'label' => Icon::show('users') . 'Пользователи',
            'url' => UserUrl::setFilters(UserSearch::class, ['/user/user/index']),
            'visible' => (bool)Yii::$app->getModule('user')
        ],
        [
            'label' => Icon::show('file-alt') . 'Контент',
            'items' => [
                ['label' => Icon::show('wrench') . 'Публичные параметры', 'url' => ['/param/index']],
                [
                    'label' => Icon::show('align-justify') . 'Тексты',
                    'url' => UserUrl::setFilters(TextSearch::class, ['/text/index'])
                ]
            ]
        ],
        [
            'label' => Icon::show('cogs') . 'Управление',
            'items' => [
                [
                    'label' => Icon::show('list-ul') . 'Лог изменений',
                    'url' => UserUrl::setFilters(Log::class, ['/log/main/index']),
                    'visible' => ($logModule->enabled && $logModule->visible)
                ], //Включить в common\config\main.php
                [
                    'label' => Icon::show('boxes') . 'Резервное копирование БД',
                    'url' => ['/backup/default/index'],
                    'visible' => (bool)Yii::$app->getModule('backup')
                ],
                [
                    'label' => Icon::show('wrench') . 'Настройки',
                    'url' => ['/setting/index']
                ],
                [
                    'label' => Icon::show('user-shield') . 'Администраторы',
                    'url' => UserUrl::setFilters(UserAdminSearch::class, ['/user-admin/index'])
                ],
                '<hr>',
                [
                    'label' => Icon::show('envelope-open') . 'Шаблоны почты',
                    'url' => ['/mail/template/index'],
                    'visible' => (bool)Yii::$app->getModule('mail')
                ],
                [
                    'label' => Icon::show('inbox') . 'Лог отправки писем',
                    'url' => UserUrl::setFilters(MailingLogSearch::class, ['/mail/mailing-log/index']),
                    'visible' => (bool)Yii::$app->getModule('mail')
                ],
                [
                    'label' => Icon::show('file-import') . 'Перенос контента на удаленный сервер',
                    'url' => ['/model-export-import/default/index'],
                    'visible' => (bool)Yii::$app->getModule('model-export-import')?->isExportEnabled
                ],
                [
                    'label' => Icon::show('file-import') . 'Лог импорта данных моделей',
                    'url' => UserUrl::setFilters(
                        ModelImportLogSearch::class,
                        ['/model-export-import/model-import-log/index']
                    ),
                    'visible' => (bool)Yii::$app->getModule('model-export-import')
                ],
                [
                    'label' => Icon::show('file-download') . Yii::t('app', 'Export Lists'),
                    'url' => UserUrl::setFilters(
                        ExportListSearch::class,
                        ['/export-list/index']
                    )
                ],
                ['label' => Icon::show('folder') . 'Файловый менеджер', 'url' => ['/site/file-manager']],
                ['label' => Icon::show('info') . 'Информация о хостинге', 'url' => ['/site/info']]
            ]
        ]
    ];
    $menuItems[] = Html::tag('div', null, ['class' => 'divider-vertical']);
    $menuItems[] = Html::tag('div', null, ['class' => 'dropdown-divider']);
    if (Yii::$app->getModule('notification')) {
        $menuItems[] = NotificationBell::widget();
    }
    $menuItems[] = Html::tag(
        'li',
        Html::a(
            sprintf('%sВыйти (%s) ', Icon::show('sign-out-alt'), Yii::$app->user->identity->username),
            ['/site/logout'],
            ['class' => 'nav-link', 'data-method' => 'POST']
        ),
        ['class' => 'nav-item skip-search']
    );
} else {
    $menuItems[] = ['label' => Icon::show('sign-in-alt') . 'Войти', 'url' => ['/site/login']];
}
echo RbacNav::widget([
    'options' => ['class' => 'nav navbar-nav ms-auto d-flex nav-pills justify-content-between'],
    'items' => $menuItems,
    'encodeLabels' => false,
    'activateParents' => true
]);
NavBar::end();
