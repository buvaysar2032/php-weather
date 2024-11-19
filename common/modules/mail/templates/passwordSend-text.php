<?php

/**
 * @var $this    yii\web\View
 * @var $message common\modules\mail\components\Message
 * @var $user    common\modules\user\models\User
 * @var $data    array
 */

$data = $data ?? [];
if (empty($data['password'])) {
    $data['password'] = 'not_set';
}

?>
Здравствуйте, <?= $data['username'] ?? '' ?>!<?= PHP_EOL ?>
Ваш новый пароль: <?= $data['password'] ?>
