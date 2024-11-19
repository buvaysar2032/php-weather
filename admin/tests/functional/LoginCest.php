<?php

namespace admin\tests\functional;

use admin\fixtures\AuthAssignmentFixture;
use admin\fixtures\UserAdminFixture;
use admin\tests\FunctionalTester;
use Exception;

/**
 * Class LoginCest
 * @package admin\tests\functional
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class LoginCest
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
            ],
            'auth_assignment' => [
                'class' => AuthAssignmentFixture::class,
                'dataFile' => codecept_data_dir() . 'auth_assignment.php'
            ]
        ];
    }

    /**
     * @throws Exception
     */
    public function checkInfo(FunctionalTester $I): void
    {
        $I->wantToTest('site info page access');
        $I->amOnPage('site/info');
        $I->dontSee('App Checkup');
        $I->dontSee('Yii2 Checkup');
        $I->dontSee('PHPinfo');
        $I->loginAdmin();
        $I->amOnPage('site/info');
        $I->see('App Checkup');
        $I->see('Yii2 Checkup');
        $I->see('PHPinfo');
        $I->dontSee('ERROR', 'div[class=bg-danger]');
    }

    /**
     * @throws Exception
     */
    public function checkPages(FunctionalTester $I): void
    {
        $I->loginAdmin();
        $I->amOnPage('user-admin/index');
        $I->see('Администраторы', 'h1');
        $I->see('Добавить Администратора');
        $I->see('admin', 'td');
        $I->see('Активен', 'span');

        $I->amOnPage('text/index');
        $I->see('Тексты', 'h1');
        $I->see('Добавить текст');
        $I->see('ID', 'a');
        $I->see('Ключ', 'a');
        $I->see('Значение', 'a');

        $I->amOnPage('user/user');
        $I->see('Пользователи', 'h1');
        $I->see('ID', 'a');
        $I->see('Никнейм', 'a');
        $I->see('Дата последней авторизации', 'a');
        $I->see('Статус', 'a');

        $I->amOnPage('setting/index');
        $I->see('Настройки', 'h1');
        $I->see('Параметр', 'a');
        $I->see('Значение', 'a');
        $I->see('Описание', 'a');
    }
}
