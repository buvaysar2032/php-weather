<?php

namespace common\modules\user;

use common\modules\user\{enums\PasswordRestoreType, models\Email};
use Yii;
use yii\i18n\PhpMessageSource;

/**
 * Class Module
 *
 * @package common\modules\user
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class Module extends \yii\base\Module
{
    public const MODULE_ERROR_MESSAGES = 'modules/user/error';

    public const MODULE_SUCCESS_MESSAGES = 'modules/user/success';

    public const MODULE_MESSAGES = 'modules/user/';

    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'common\modules\user\controllers';

    /**
     * {@inheritdoc}
     */
    public $defaultRoute = 'user';

    //>>> Регистрация/авторизация через почту
    /**
     * Разрешить верификацию E-mail'ов
     */
    public bool $enableEmailVerification = true;

    /**
     * Автоматически отправлять письмо для подтверждения почты
     */
    public bool $autoSendVerificationEmail = true;

    /**
     * Шаблон письма для подтверждения почты
     */
    public string $verificationEmailTemplate = 'email-confirm';

    //>>> Регистрация/авторизация через соц. сети
    /**
     * Разрешить авторизацию через соц. сети
     */
    public bool $enableSocAuthorization = true;

    /**
     * Нужно ли регистрировать пользователя при попытке авторизации с непривязанной соц. сетью
     */
    public bool $registerIfNot = true;

    /**
     * Считать e-mail из соц. сети подтверждённым по умолчанию
     */
    public bool $autoVerifyEmailFromSocNet = true;

    /**
     * Возвращать ли пользователя на страницу регистрации для дозаполнения данных TODO
     */
    public bool $enableRedirectToSignup = true;

    //>>>Восстановление пароля
    /**
     * Разрешить восстанавливать пароль
     */
    public bool $enablePasswordRestore = true;

    /**
     * Восстановления пароля напрямую или с помощью токена
     */
    public PasswordRestoreType $passwordRestoreType = PasswordRestoreType::Directly;

    /**
     * Шаблон для отправки письма с паролем напрямую
     */
    public string $passwordSendTemplate = 'passwordSend';

    /**
     * Шаблон для отправки токена для восстановления пароля
     */
    public string $passwordTokenTemplate = 'passwordResetToken'; //

    //>>>Изменение пользователя
    /**
     * Список полей, подлежащих изменению
     */
    public array $updateFields = ['email', 'username', 'first_name', 'middle_name', 'last_name', 'phone'];

    /**
     * Отправка письма для подтверждения e-mail в случае изменения адреса
     */
    public bool $sendVerificationMessageIfEmailIsChanged = true;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();
        self::initI18N();
    }

    public static function initI18N(): void
    {
        Yii::$app->i18n->translations[self::MODULE_MESSAGES . '*'] = [
            'class' => PhpMessageSource::class,
            'basePath' => '@root/common/modules/user/messages',
            'fileMap' => [
                self::MODULE_MESSAGES => 'app.php',
                self::MODULE_ERROR_MESSAGES => 'error.php',
                self::MODULE_SUCCESS_MESSAGES => 'success.php'
            ]
        ];
    }
}
