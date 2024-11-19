<?php

/**
 * @var $this    yii\web\View
 * @var $message common\modules\mail\components\Message
 * @var $user    common\modules\user\models\User
 * @var $data    array
 */

$data = $data ?? [];
if (empty($data['resetLink']) && $user) {
    $data['resetLink'] = "{$data['domain']}/site/reset-password?token=$user->password_reset_token";
}
?>
Здравствуйте, <?= $data['username'] ?? '' ?>!<?= PHP_EOL ?>
<?= PHP_EOL ?>
Вы получили это письмо, так как нам поступил запрос на <?= PHP_EOL ?>
восстановление Вашего пароля на сайте <?= $data['domain'] ?>.<?= PHP_EOL ?>
<?= PHP_EOL ?>
Для обновления пароля, пожалуйста, перейдите по <?= PHP_EOL ?>
ссылке: <?= $data['resetLink'] ?><?= PHP_EOL ?>
<?= PHP_EOL ?>
Если Вы не обращались к процедуре восстановления<?= PHP_EOL ?>
пароля, просто проигнорируйте данное письмо. <?= PHP_EOL ?>
Ваш пароль не будет изменен.
