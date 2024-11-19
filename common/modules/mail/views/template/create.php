<?php

use common\modules\mail\{Mail, models\Template};
use yii\bootstrap5\Html;

/**
 * @var $this     yii\web\View
 * @var $template Template
 */

$this->title = Yii::t(Mail::MODULE_MESSAGES, 'Create Template');
$this->params['breadcrumbs'][] = [
    'label' => Yii::t(Mail::MODULE_MESSAGES, 'Templates'),
    'url' => ['index']
];
$this->params['breadcrumbs'][] = $this->title;

//АВТОЗАПОЛНЕНИЕ НОВОГО ШАБЛОНА
$template->pugHtml = <<<PUG
p
    | Здравствуйте,&nbsp;
    =username
p
    | Поздравляем Вас с успешной регистрацией на сайте&nbsp;
    =domain
    |.
PUG;

$template->text = <<<'PHP'
<?php

/**
 * @var $this    yii\web\View
 * @var $message common\modules\mail\components\Message
 * @var $user    common\modules\user\models\User|null
 * @var $data    array
 */
$data = $data ?? [];
?>

<!-- ТЕЛО ПИСЬМА -->
PHP;
?>

<div class="mail-template-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', ['template' => $template]) ?>

</div>
