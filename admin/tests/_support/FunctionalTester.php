<?php

namespace admin\tests;

use Codeception\Actor;
use Codeception\Lib\Friend;
use Exception;
use Yii;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
 */
class FunctionalTester extends Actor
{
    use _generated\FunctionalTesterActions;

    /**
     * @throws Exception
     */
    public function loginAdmin(): void
    {
        $this->amOnPage('site/login');
        $this->see('Имя пользователя', 'label');
        $this->see('Пароль', 'label');
        $this->fillField('Имя пользователя', 'admin');
        $this->fillField('Пароль', 'password_0');
        $this->click('login-button');
        $this->wait(1);
        $this->dontSee('Неверный логин или пароль');
    }

    public function loginModerator(): void
    {
        $this->amOnPage('site/login');
        $this->see('Имя пользователя', 'label');
        $this->see('Пароль', 'label');
        $this->fillField('Имя пользователя', 'moderator');
        $this->fillField('Пароль', 'password_0');
        $this->click('login-button');
        $this->wait(1);
        $this->dontSee('Неверный логин или пароль');
    }
}
