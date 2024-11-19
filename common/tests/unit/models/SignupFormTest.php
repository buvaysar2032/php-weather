<?php

namespace common\tests\unit\models;

use Codeception\Test\Unit;
use common\enums\Boolean;
use common\fixtures\{EmailFixture, UserAgentFixture, UserExtFixture, UserFixture};
use common\modules\user\models\SignupForm;
use common\modules\user\models\User;
use common\tests\UnitTester;

/**
 * Class SignupFormTest
 * @package common\tests\unit\models
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class SignupFormTest extends Unit
{
    protected UnitTester $tester;

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
     * @throws \common\components\exceptions\ModelSaveException
     * @throws \yii\base\Exception
     */
    public function testCorrectSignup(): void
    {
        $model = new SignupForm(
            [
                'username' => 'some_username',
                'email' => 'some_email@example.com',
                'password' => 'some_password',
                'rules_accepted' => Boolean::Yes->value,
            ]
        );

        $user = $model->signup();

        expect($user)->toBeInstanceOf(User::class);
        expect($user->username)->toEqual('some_username');
        expect($user->email->value)->toEqual('some_email@example.com');
        expect($user->validatePassword('some_password'))->toBeTrue();
    }

    /**
     * @throws \common\components\exceptions\ModelSaveException
     * @throws \yii\base\Exception
     */
    public function testNotCorrectSignup(): void
    {
        $model = new SignupForm(
            [
                'username' => 'KropMih',
                'email' => 'krop5111@gmail.com',
                'password' => 'password_0',
                'rules_accepted' => 0,
            ]
        );

        expect($model->signup())->toBeEmpty();
        expect($model->getErrors('username'))->notToBeNull();
        expect($model->getErrors('email'))->notToBeNull();
        expect($model->getFirstError('username'))->toEqual('Such Username is already registered');
        expect($model->getFirstError('email'))->toEqual('Such Email is already registered');
    }
}
