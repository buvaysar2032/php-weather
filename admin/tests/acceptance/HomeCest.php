<?php

namespace admin\tests\acceptance;

use admin\tests\AcceptanceTester;

/**
 * Class HomeCest
 * @package admin\tests\acceptance
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class HomeCest
{
    public function checkHome(AcceptanceTester $I): void
    {
        $I->amOnPage('site/login');

        $I->see('Пожалуйста заполните указанные ниже поля:');
    }
}
