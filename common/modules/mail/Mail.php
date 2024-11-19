<?php

namespace common\modules\mail;

use common\models\Setting;
use common\modules\mail\components\Mailer;
use Yii;
use yii\base\{InvalidConfigException, Module};
use yii\helpers\ArrayHelper;
use yii\i18n\PhpMessageSource;
use yii\web\NotFoundHttpException;

/**
 * mail module definition class
 *
 * @package mail
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class Mail extends Module
{
    public const MODULE_MESSAGES = 'modules/mail/';

    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'common\modules\mail\controllers';

    public array $mailerOptions = [
        'enableMailerLogging' => true
    ];

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function init(): void
    {
        parent::init();
        if (!isset($this->mailerOptions['transport'])) {
            $this->mailerOptions['transport'] = [
                'scheme' => 'smtp',
                'host' => $host = Yii::$app->environment->EMAIL_HOST ?: Setting::getParameterValue('email_server'),
                'port' => $port = (int)(Yii::$app->environment->EMAIL_PORT ?: Setting::getParameterValue('email_port')),
                'username' => Yii::$app->environment->EMAIL_USERNAME ?: Setting::getParameterValue('email_username'),
                'password' => Yii::$app->environment->EMAIL_PASSWORD ?: Setting::getParameterValue('email_password'),
                'options' => $port === 25
                    ? ['allow_self_signed' => true, 'verify_peer' => false, 'verify_peer_name' => false]
                    : ['ssl' => true]
            ];
        }
        if (!isset($this->mailerOptions['useFileTransport'])) {
            $this->mailerOptions['useFileTransport'] = YII_ENV_TEST || empty($host) || $host === 'smtp.test.ru';
        }
        if (!isset($this->mailerOptions['from'])) {
            $nameFrom = Yii::$app->environment->EMAIL_NAME_FROM ?:Setting::getParameterValue('email_name_from');
            $from = Yii::$app->environment->EMAIL_FROM ?: Setting::getParameterValue('email_from');
            $this->mailerOptions['from'] = !empty($nameFrom) ? [$from => $nameFrom] : [$from];
        }

        Yii::$app->set('mailer', ArrayHelper::merge(['class' => Mailer::class], $this->mailerOptions));
        Yii::$app->i18n->translations[self::MODULE_MESSAGES . '*'] = [
            'class' => PhpMessageSource::class,
            'basePath' => '@root/common/modules/mail/messages',
            'fileMap' => [
                self::MODULE_MESSAGES => 'app.php',
                self::MODULE_MESSAGES . 'error' => 'error.php',
                self::MODULE_MESSAGES . 'success' => 'success.php'
            ]
        ];
    }
}
