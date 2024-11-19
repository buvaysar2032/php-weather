<?php

/**
 * @var $this    yii\web\View
 * @var $message common\modules\mail\components\Message
 * @var $user    common\modules\user\models\User
 * @var $data    array
 */

$data = $data ?? [];
if (empty($data['confirmLink']) && $user) {
    $data['confirmLink'] = "{$data['domain']}/api/v1/user/email-confirm?token={$user->email->confirm_token}";
}
?>
Здравствуйте, <?= $data['username'] ?? '' ?><?= PHP_EOL ?>
поздравляем с успешной регистрацией на сайте <?= $data['domain'] ?><?= PHP_EOL ?>
Для завершения регистрации на сайте перейдите по ссылке: <?= PHP_EOL ?>
<?= $data['confirmLink'] ?>
