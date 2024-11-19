<?php
$users = \common\modules\user\models\User::find()->all();
return [
    [
        'user_id' => $users[0]->id,
        'value' => 'Codeception_PHPUnit',
        'auth_key' => 'Neiy-dwCPyRWH7kefvIvY5wkuPOsexHh'
    ]
];