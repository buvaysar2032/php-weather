<?php

namespace common\tests\unit\models;

use Codeception\Test\Unit;
use common\enums\Boolean;
use common\modules\user\enums\Status;
use common\fixtures\{EmailFixture, UserAgentFixture, UserExtFixture, UserFixture};
use common\modules\user\models\{Email, User};

/**
 * User identity test
 * @package common\tests\unit\models
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class UserTest extends Unit
{
    public function _fixtures(): array
    {
        return [
            'user' => [
                'class' => UserFixture::class,
                'dataFile' => codecept_data_dir() . 'user.php'
            ],
            'user_ext' => [
                'class' => UserExtFixture::class,
                'dataFile' => codecept_data_dir() . 'user_ext.php'
            ],
            'email' => [
                'class' => EmailFixture::class,
                'dataFile' => codecept_data_dir() . 'email.php'
            ],
            'user_agent' => [
                'class' => UserAgentFixture::class,
                'dataFile' => codecept_data_dir() . 'user_agent.php'
            ]
        ];
    }

    /**
     * @throws \yii\web\HttpException
     */
    public function testFindByUsername(): void
    {
        /* @var User $user */
        $user = User::findIdentityByUsername('some_username');
        expect($user)->toBeNull('user not found');
        $user = User::findIdentityByUsername('KropMih');
        expect($user)->toBeInstanceOf(User::class, 'user found');
    }

    public function testFindByEmail(): void
    {
        /* @var User $user */
        $user = User::findIdentityByEmail('some@email.net');
        expect($user)->toBeNull('user not found');
        $user = User::findIdentityByEmail('krop5111@gmail.com');
        expect($user)->toBeInstanceOf(User::class, 'user found');
    }

    /**
     * @throws \yii\web\HttpException
     */
    public function testFindIdentityByAccessToken(): void
    {
        /* @var User $user */
        $user = User::findIdentityByAccessToken('some_token');
        expect($user)->toBeNull('user not found');
        $user = User::findIdentityByAccessToken('Neiy-dwCPyRWH7kefvIvY5wkuPOsexHh');
        expect($user)->toBeInstanceOf(User::class, 'user found');
    }

    public function testValidateToken(): void
    {
        /* @var User $user */
        $user = User::find()->one();
        expect($user->validateAuthKey('some_incorrect_auth_key'))->toBeFalse('token should not be valid');
        expect($user->validateAuthKey('Neiy-dwCPyRWH7kefvIvY5wkuPOsexHh'))->toBeTrue('token should be valid');
    }

    /**
     * @throws \common\components\exceptions\ModelSaveException
     * @throws \yii\base\Exception
     */
    public function testConfirmEmail(): void
    {
        /* @var \common\modules\user\models\Email $email */
        $email = Email::find()->one();
        expect($email->is_confirmed)->toEqual(Boolean::No->value, 'email should not be confirmed');
        $email->generateConfirmToken();
        $email->save();
        expect($email->confirm_token)->toBeString('confirm token should be set');
        Email::confirm($email->confirm_token);
        $email->refresh();
        expect($email->is_confirmed)->toEqual(Boolean::Yes->value, 'email should be confirmed');
        expect($email->user->status)->toEqual(Status::Active->value, 'user should be active');
    }

    public function testProfile(): void
    {
        $user = User::find()->one();
        $profile = $user->profile;
        expect($profile['access_token'])->notToBeNull('access_token should be set');
        expect($profile['email'])->toEqual('krop5111@gmail.com', 'email should be set');
        expect($profile['is_email_confirmed'])->toBeBool('is_email_confirmed should be set');
    }
}