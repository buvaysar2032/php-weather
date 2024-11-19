<?php

namespace common\modules\log;

use Yii;
use yii\base\Module;
use yii\i18n\PhpMessageSource;

/**
 * log module definition class
 *
 * @package log
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class Log extends Module
{
    public const MODULE_MESSAGES = 'modules/log/';

    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'common\modules\log\controllers';

    /**
     * Статус активности логирования
     */
    public bool $enabled = false;

    /**
     * Видно ли в меню
     */
    public bool $visible = true;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();
        Yii::$app->i18n->translations[self::MODULE_MESSAGES . '*'] = [
            'class' => PhpMessageSource::class,
            'basePath' => '@root/common/modules/log/messages',
            'fileMap' => [
                self::MODULE_MESSAGES => 'app.php',
                self::MODULE_MESSAGES . 'error' => 'error.php'
            ]
        ];
    }
}
