<?php

use common\enums\Boolean;
use common\modules\user\models\User;

/** @var User[] $users */
$users = User::find()->all();
return [
    [
        'user_id' => $users[0]->id,
        'value' => 'krop5111@gmail.com',
        'is_confirmed' => Boolean::Yes->value,
    ]
];