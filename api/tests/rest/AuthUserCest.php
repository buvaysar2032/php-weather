<?php

namespace api\tests\rest;

use api\tests\ApiTester;
use common\fixtures\{EmailFixture, UserAgentFixture, UserExtFixture, UserFixture};

/**
 * Class AuthUserCest
 * @package admin\tests\rest
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class AuthUserCest
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

    public function getProfile(ApiTester $I): void
    {
        $I->amBearerAuthenticated('Neiy-dwCPyRWH7kefvIvY5wkuPOsexHh');
        $I->haveHttpHeader('User-Agent', 'Codeception_PHPUnit');
        $I->sendGET('/user/profile');
        $I->seeSuccessJsonResponse();
        $I->expectTo('see current user data');
        $I->seeResponseContains('"username": "KropMih"');
        $I->seeResponseContains('"email": "krop5111@gmail.com"');
        $I->seeResponseContains('"is_email_confirmed": true');
        $I->seeResponseContains('"access_token": "Neiy-dwCPyRWH7kefvIvY5wkuPOsexHh"');
    }

    public function updateProfile(ApiTester $I): void
    {
        $I->amBearerAuthenticated('Neiy-dwCPyRWH7kefvIvY5wkuPOsexHh');
        $I->haveHttpHeader('User-Agent', 'Codeception_PHPUnit');
        $I->sendPOST('/user/update', ['username' => 'KropMih51', 'email' => 'krop2111@gmail.com']);
        $I->seeSuccessJsonResponse();
        $I->expectTo('see updated user data');
        $I->seeResponseContains('"access_token": "Neiy-dwCPyRWH7kefvIvY5wkuPOsexHh"');
        $I->seeResponseContains('"username": "KropMih51"');
        $I->seeResponseContains('"email": "krop2111@gmail.com"');
        $I->seeResponseContains('"is_email_confirmed": false');
    }

    public function testLogout(ApiTester $I): void
    {
        $I->amBearerAuthenticated('Neiy-dwCPyRWH7kefvIvY5wkuPOsexHh');
        $I->haveHttpHeader('User-Agent', 'Codeception_PHPUnit');
        $I->sendGET('/user/logout');
        $I->seeSuccessJsonResponse();
        $I->seeResponseContains('"user": "Вы успешно вышли из системы"');
    }
}