<?php

namespace admin\tests\functional;

use admin\fixtures\UserAdminFixture;
use admin\tests\FunctionalTester;

/**
 * Class RbacCest
 * @package admin\tests\functional
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * TODO: написать тесты для текущей реализации rbac
 */
class RbacCest
{
    /**
     * Load fixtures before db transaction begin
     * Called in _before()
     * @see \Codeception\Module\Yii2::_before()
     * @see \Codeception\Module\Yii2::loadFixtures()
     */
    public function _fixtures(): array
    {
        return [
            'user' => [
                'class' => UserAdminFixture::class,
                'dataFile' => codecept_data_dir() . 'user.php'
            ]
        ];
    }

    public function checkBan(FunctionalTester $I): void
    {
        $I->amOnPage('site/login');
        $I->see('Имя пользователя', 'label');
        $I->see('Пароль', 'label');
        $I->fillField('Имя пользователя', 'banned');
        $I->fillField('Пароль', 'password_0');
        $I->click('login-button');
        $I->wait(1);
        $I->see('Неверный логин или пароль');
    }
}