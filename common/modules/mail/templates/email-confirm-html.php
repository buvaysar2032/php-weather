<?php

/**
 * @var $this    yii\web\View
 * @var $message common\modules\mail\components\Message
 * @var $user    common\modules\user\models\User|null
 * @var $data    array
 */

$data = $data ?? [];
if (empty($data['confirmLink']) && $user) {
    $data['confirmLink'] = "{$data['domain']}/api/v1/user/email-confirm?token={$user->email->confirm_token}";
}
$this->registerCss(file_get_contents(__DIR__ . '/' . str_replace('-html.php', '.css', basename($this->viewFile))));
echo $this->render(str_replace('-html.php', '.pug', basename($this->viewFile)), $data);
