<?php

use admin\modules\rbac\components\RbacHtml;
use common\modules\mail\{Mail, models\Template};
use yii\helpers\Json;
use yii\web\View;

/**
 * @var $this     View
 * @var $template Template
 */

$this->title = $template->name;
$this->params['breadcrumbs'][] = [
    'label' => Yii::t(Mail::MODULE_MESSAGES, 'Templates'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = $this->title;
$textFile = Template::getTextFilename($template->name);
$pugLayout = Json::htmlEncode($template->pugLayout);
$layoutStyle = Json::htmlEncode($template->layoutStyle);
$pugHtml = Json::htmlEncode($template->pugHtml);
$style = Json::htmlEncode($template->style);
$url = '/admin/mail/template/render-pug';
if ($renderAvailable = RbacHtml::isAvailable($url)) {
    $this->registerJs(
        <<<JS
function writeIframeText(text) {
  const iframe = document.getElementById('html-preview')
  const doc = iframe.contentWindow?.document || iframe.contentDocument
  doc.open()
  doc.write(text)
  doc.close()
  setTimeout(() => {
    const body = doc.body
    iframe.style.width = '100%'
    iframe.style.height = (body.offsetHeight + body.scrollHeight - body.clientHeight) + 'px'
    iframe.style.maxHeight = '80vh'
    iframe.style.minWidth = '600px'
  }, 50)
}
$.post('$url', { layout: '$pugLayout', layoutStyle: '$layoutStyle', content: '$pugHtml', style: '$style' })
  .then(response => writeIframeText(response))
  .catch(reason => writeIframeText('<pre>' + reason.responseText + '</pre>'))
JS
    );
}
?>
<div class="mail-template-view">

    <h1><?= RbacHtml::encode($this->title) ?></h1>

    <p>
        <?= RbacHtml::a(
            Yii::t('app', 'Update'),
            ['update', 'name' => $template->name],
            ['class' => 'btn btn-primary']
        ) ?>
        <?= RbacHtml::a(Yii::t('app', 'Delete'), ['delete', 'name' => $template->name], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post'
            ]
        ]) ?>
    </p>

    <?php if ($renderAvailable): ?>
        <strong>
            <?= Yii::t(Mail::MODULE_MESSAGES, 'Html Template') ?>
        </strong>
        <div>
            <iframe id="html-preview"></iframe>
        </div>
    <?php endif ?>
    <b><?= Yii::t(Mail::MODULE_MESSAGES, 'Text Template') ?></b>
    <div class="mail-preview">
        <?php
        if (file_exists(Yii::getAlias($textFile))) {
            echo nl2br(Yii::$app->view->renderFile($textFile, [
                'user' => Template::getDummyUser(),
                'data' => ['domain' => Yii::$app->request->hostInfo]
            ]));
        } else {
            echo 'NOT FOUND';
        } ?>
    </div>
</div>
