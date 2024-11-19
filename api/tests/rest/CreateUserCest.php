<?php

namespace api\tests\rest;

use api\tests\ApiTester;
use Codeception\Util\HttpCode;
use common\fixtures\{EmailFixture, UserAgentFixture, UserExtFixture, UserFixture};

/**
 * Class CreateUserCest
 *
 * @package api\tests\acceptance
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class CreateUserCest
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
            'user_agent' => [
                'class' => UserAgentFixture::class,
                'dataFile' => codecept_data_dir() . 'user_agent.php'
            ],
            'email' => [
                'class' => EmailFixture::class,
                'dataFile' => codecept_data_dir() . 'email.php'
            ]
        ];
    }

    public function sendIncorrectRequest(ApiTester $I): void
    {
        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendPOST('/user', ['name' => 'KropMih51', 'email' => 'krop2111@gmail.com']);
        $I->seeErrorJsonResponse(HttpCode::NOT_FOUND); // 400
    }

    public function sendIncorrectSignupData(ApiTester $I): void
    {
        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendPOST(
            '/user/signup',
            ['username' => 'KropMih', 'email' => 'krop5111@gmail.com', 'password' => 'user123']
        );
        $I->seeErrorJsonResponse(HttpCode::INTERNAL_SERVER_ERROR); // 500
    }

    public function createUserViaApi(ApiTester $I): void
    {
        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendPOST(
            '/user/signup',
            [
                'username' => 'KropMih51',
                'email' => 'krop2111@gmail.com',
                'password' => 'user123',
                'rules_accepted' => 1
            ]
        );
        $I->seeSuccessJsonResponse();
    }
}