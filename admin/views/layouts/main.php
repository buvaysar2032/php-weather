<?php

use admin\assets\{AdminAsset, FontAwesomeAsset, VueAsset};
use admin\modules\rbac\components\RbacSideNav;
use admin\widgets\tooltip\TooltipTriggerWidget;
use common\widgets\Alert;
use kartik\sidenav\SideNav;
use yii\bootstrap5\{Breadcrumbs, Html};
use yii\helpers\Url;
use yii\web\View;

/**
 * @var $this    View
 * @var $content string
 */

AdminAsset::register($this);
FontAwesomeAsset::register($this);
VueAsset::register($this);

$containerClass = (array_key_exists('layout_class', $this->params) && $this->params['layout_class'])
    ? $this->params['layout_class']
    : 'container';
$breadcrumbStyle = str_contains($containerClass, 'container-fluid') ? 'style="padding-top: 20px"' : '';
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Yii::$app->name ?> | Панель администратора | <?= $this->title ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div id="app">
    <div class="wrap">
        <?= $this->render('_menu') ?>
        <div class="container-fluid">
            <div class="row flex-nowrap">
                <?php if (!empty($this->params['sidebar'])): ?>
                    <div class="col-auto col-md-3 col-xl-2">
                        <?= RbacSideNav::widget([
                            'type' => SideNav::TYPE_SECONDARY,
                            'heading' => $this->params['sidebarHeader'] ?? 'Options',
                            'items' => $this->params['sidebar']
                        ]) ?>
                    </div>
                <?php endif ?>
                <div class="col py-3">
                    <div class="content-wrapper <?= $containerClass ?>">
                        <nav aria-label="breadcrumb" class="mb-3" <?= $breadcrumbStyle ?>>
                            <?= Breadcrumbs::widget([
                                'homeLink' => ['label' => Yii::t('yii', 'Home'), 'url' => Url::to(['/'])],
                                'links' => $this->params['breadcrumbs'] ?? []
                            ]) ?>
                        </nav>
                        <?= Alert::widget(['options' => ['class' => 'show', 'role' => 'alert']]) ?>
                        <?= $content ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer class="footer <?= Yii::$app->themeManager->isDark ? 'bg-dark' : 'bg-light' ?>">
        <div class="container">
            <div class="float-start" style="margin-bottom:unset">
                <?= TooltipTriggerWidget::widget() ?>
            </div>
            <div class="float-end">
                <?= Yii::$app->themeManager->renderSwitchButton() ?>
                <?= Html::a('На сайт', '/', [
                    'class' => ['btn', 'btn-sm', Yii::$app->themeManager->isDark ? 'btn-secondary' : 'btn-dark'],
                    'style' => ['margin' => '-3px 0 0 5px']
                ]) ?>
            </div>
        </div>
    </footer>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>

