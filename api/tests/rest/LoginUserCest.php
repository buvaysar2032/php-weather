<?php

namespace api\tests\rest;

use api\tests\ApiTester;
use Codeception\Util\HttpCode;
use common\fixtures\{EmailFixture, UserAgentFixture, UserExtFixture, UserFixture};

/**
 * Class LoginUserCest
 * @package admin\tests\rest
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class LoginUserCest
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

    public function incorrectLogin(ApiTester $I): void
    {
        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->haveHttpHeader('User-Agent', 'Codeception_PHPUnit');
        $I->sendPOST('/user/login', ['login' => 'krop5111@gmail.com', 'password' => 'incorrectPassword']);
        $I->seeErrorJsonResponse(HttpCode::INTERNAL_SERVER_ERROR);
    }

    public function successLogin(ApiTester $I): void
    {
        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->haveHttpHeader('User-Agent', 'Codeception_PHPUnit');
        $I->sendPOST('/user/login', ['login' => 'krop5111@gmail.com', 'password' => 'password_0']);
        $I->seeSuccessJsonResponse();
        $I->expectTo('to see current user data with new access token');
        $I->seeResponseContains('"access_token": '); // any new token
        $I->seeResponseContains('"username": "KropMih"');
        $I->seeResponseContains('"email": "krop5111@gmail.com"');
    }
}