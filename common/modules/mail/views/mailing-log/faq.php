<?php

use admin\widgets\faq\FaqWidget;
use common\components\helpers\UserUrl;
use common\modules\mail\{Mail, models\MailingLogSearch};

/* @var $this yii\web\View */

$this->title = Yii::t(Mail::MODULE_MESSAGES, 'Errors FAQ');
$this->params['breadcrumbs'][] = [
    'label' => Yii::t(Mail::MODULE_MESSAGES, 'Mailing Logs'),
    'url' => UserUrl::setFilters(MailingLogSearch::class)
];
$this->params['breadcrumbs'][] = $this->title;

$failed_auth = <<<HTML
<b>Failed to authenticate on SMTP server</b>
<p>
Ошибка авторизации на почтовом сервере, проверьте правильность email и пароля в настройках.
</p>
<b>Connection could not be established with host</b>
<p>
Ошибка подключения к почтовому серверу, проверьте правильность адреса и порта от почтового сервера.
</p>
HTML;

$templateErrors = <<<HTML
<b>ParseError</b>
<p>Ошибка чтения шаблона, проверьте шаблон на синтаксические ошибки.</p>
<b>The view file does not exist: < домен >\common/mail\< шаблон >-html.php</b>
<p>Отсутствует файл шаблона в папке common/mail. Проверьте правильность пути к файлу и наличие файла по данному пути</p>
<b>Access Denied</b>
<p>Нет доступа к папке с шаблонами, откройте доступ к чтению/изменению/созданию файлов в папке mail</p>
HTML;

?>

<?= FaqWidget::widget([
    'config' => [
        'Ошибки авторизации' => $failed_auth,
        'Ошибки шаблона' => $templateErrors
    ]
]) ?>
