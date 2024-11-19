<?php

namespace common\tests\unit\models;

use Codeception\Test\Unit;
use common\fixtures\{EmailFixture, UserAgentFixture, UserExtFixture, UserFixture};
use common\modules\user\models\LoginForm;
use common\tests\UnitTester;
use Yii;

/**
 * Login form test
 * @package common\tests\unit\models
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class LoginFormTest extends Unit
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
     * @throws \yii\web\HttpException
     */
    public function testLoginNoUser(): void
    {
        $model = new LoginForm(
            [
                'login' => 'not_existing_username',
                'password' => 'not_existing_password',
            ]
        );

        expect($model->login())->toBeFalse('model should not login user');
        expect(Yii::$app->user->isGuest)->toBeTrue('user should not be logged in');
    }

    /**
     * @throws \common\components\exceptions\ModelSaveException
     * @throws \yii\base\Exception
     * @throws \yii\web\HttpException
     */
    public function testLoginWrongPassword(): void
    {
        $model = new LoginForm(
            [
                'login' => 'KropMih',
                'password' => 'wrong_password',
            ]
        );

        expect($model->login())->toBeFalse('model should not login user');
        expect($model->errors)->arrayToHaveKey('password', 'error message should be set');
        expect(Yii::$app->user->isGuest)->toBeTrue('user should not be logged in');
    }

    /**
     * @throws \common\components\exceptions\ModelSaveException
     * @throws \yii\base\Exception
     * @throws \yii\web\HttpException
     */
    public function testLoginCorrect(): void
    {
        $model = new LoginForm(
            [
                'login' => 'KropMih',
                'password' => 'password_0',
            ]
        );

        expect($model->login())->toBeTrue('model should login user');
        expect($model->errors)->arrayNotToHaveKey('password', 'error message should not be set');
        expect(Yii::$app->user->isGuest)->toBeFalse('user should be logged in');
    }
}
