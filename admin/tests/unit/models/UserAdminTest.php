<?php

namespace admin\tests\unit\models;

use admin\enums\AdminStatus;
use admin\fixtures\UserAdminFixture;
use admin\models\UserAdmin;
use admin\tests\UnitTester;
use Codeception\Test\Unit;

/**
 * Class UserAdminTest
 * @package admin\tests\unit
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class UserAdminTest extends Unit
{
    protected UnitTester $tester;

    public function _fixtures(): array
    {
        return [
            'user' => [
                'class' => UserAdminFixture::class,
                'dataFile' => codecept_data_dir() . 'user.php'
            ]
        ];
    }

    public function testValidateWrongData(): void
    {
        $model = new UserAdmin(
            [
                'username' => 'wrong%username',
                'email' => 'wrong%email',
                'status' => -1,
            ]
        );
        expect($model->validate())->toBeFalse('model is not valid');
        expect($model->errors)->arrayToHaveKey('username', 'username is incorrect');
        expect($model->errors)->arrayToHaveKey('email', 'email is incorrect');
        expect($model->errors)->arrayToHaveKey('status', 'status is incorrect');
    }

    public function testValidateExistingData(): void
    {
        $model = new UserAdmin(
            [
                'username' => 'admin',
                'email' => 'admin@admin.com',
                'status' => AdminStatus::Inactive->value,
            ]
        );
        expect($model->validate())->toBeFalse('model is not valid');
        expect($model->errors)->arrayToHaveKey('username', 'username exists in errors');
        expect($model->errors)->arrayToHaveKey('email', 'email exists in errors');
    }

    public function testValidateCorrectData(): void
    {
        $model = new UserAdmin(
            [
                'username' => 'other_user',
                'email' => 'other@example.com',
                'status' => AdminStatus::Active->value,
                'auth_key' => 'EdKfXrx88weFMV0vIxuTMWKgfK2t3Lp1',
                'password_hash' => '$2y$13$g5nv41Px7VBqhS3hVsVN2.MKfgT3jFdkXEsMC4rQJLfaMa7VaJqL22',
            ]
        );
        expect($model->validate())->toBeTrue('model is valid');
    }

    /**
     * @throws \yii\base\Exception
     */
    public function testSave(): void
    {
        $model = new UserAdmin(
            [
                'scenario' => UserAdmin::SCENARIO_REGISTER,
                'username' => 'test_user',
                'email' => 'other@example.com',
                'status' => AdminStatus::Active->value,
                'auth_key' => 'EdKfXrx88weFMV0vIxuTMWKgfK2t3Lp1'
            ]
        );
        $model->setPassword('new-password');
        expect($model->save())->toBeTrue('model is saved');
        expect($model->validatePassword('new-password'))->toBeTrue('password is correct');
        expect($model->auth_key)->notToBeEmpty('auth key is correct');
        expect($model->created_at)->notToBeEmpty('created_at is correct');
        expect($model->updated_at)->notToBeEmpty('updated_at is correct');
    }

    public function testRules(): void
    {
        $model = new UserAdmin();
        expect($model->rules())->toBeArray('Rules return array');
    }
}