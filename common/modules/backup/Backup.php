<?php

namespace common\modules\backup;

use Yii;
use yii\base\Module;
use yii\i18n\PhpMessageSource;

/**
 * backup module definition class
 *
 * @package common\modules\backup
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class Backup extends Module
{
    public const MODULE_MESSAGES = 'modules/backup/';

    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'common\modules\backup\controllers';

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();
        Yii::$app->i18n->translations[self::MODULE_MESSAGES . '*'] = [
            'class' => PhpMessageSource::class,
            'basePath' => '@root/common/modules/backup/messages',
            'fileMap' => [
                self::MODULE_MESSAGES => 'app.php',
                self::MODULE_MESSAGES . 'error' => 'error.php'
            ]
        ];
    }
}
