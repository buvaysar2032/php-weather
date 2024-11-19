<?php

namespace admin\modules\modelExportImport;

use common\models\Setting;
use Yii;
use yii\base\Module;
use yii\i18n\PhpMessageSource;
use yii\web\NotFoundHttpException;

/**
 * model-export-import module definition class
 *
 * @package modelExportImport
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property-read bool $isExportEnabled
 */
class ModelExportImport extends Module
{
    public const MODULE_MESSAGES = 'modules/model-export-import/';
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'admin\modules\modelExportImport\controllers';

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();
        Yii::$app->i18n->translations[self::MODULE_MESSAGES . '*'] = [
            'class' => PhpMessageSource::class,
            'basePath' => '@root/admin/modules/modelExportImport/messages',
            'fileMap' => [
                self::MODULE_MESSAGES => 'app.php',
                self::MODULE_MESSAGES . 'error' => 'error.php'
            ]
        ];
    }

    /**
     * @throws NotFoundHttpException
     */
    public function getIsExportEnabled(): bool
    {
        return (bool)Setting::getParameterValue('remote_import_url', true);
    }
}
