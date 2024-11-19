<?php

namespace admin\models;

use common\components\{ExtendedZip, helpers\UserFileHelper};
use common\models\AppModel;
use Yii;
use yii\base\Exception;
use ZipArchive;

/**
 * Class SiteExportForm
 *
 * @package models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class SiteExportForm extends AppModel
{
    /**
     * Экспортировать ли изображения
     */
    public bool $exportImages = false;

    /**
     * Экспортировать ли базу данных
     */
    public bool $exportDb = true;

    /**
     * Имя архива
     */
    public ?string $filename = null;

    /**
     * Список исключений для бекапа
     */
    public array $exceptions = [];

    /**
     * {@inheritdoc}
     */
    final public function rules(): array
    {
        return [
            [['exportDb', 'exportImages'], 'required'],
            [['exportDb', 'exportImages'], 'boolean']
        ];
    }

    /**
     * {@inheritdoc}
     */
    final public function attributeLabels(): array
    {
        return [
            'exportDb' => Yii::t('app', 'Export DB'),
            'exportImages' => Yii::t('app', 'Export Images')
        ];
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    final public function init(): void
    {
        parent::init();
        $this->exceptions = [
            '/.git',
            '/.idea',
            '/.phpdoc',
            '/.vagrant',
            '/vendor',
            '/node_modules',
            '/admin/runtime',
            '/api/runtime',
            //            '/common/runtime',// Там могут быть файлы модуля UserFileUpload
            '/console/runtime',
            '/frontend/runtime',
            '/htdocs/assets',
            '/htdocs/admin/assets',
            '/htdocs/api/assets'
        ];
        $path = Yii::getAlias('@admin/runtime') . '/backup/';
        UserFileHelper::createDirectory($path);
        $this->filename = $path . $_SERVER['SERVER_NAME'] . '.zip';
    }

    /**
     * Экспортировать весь сайт в архив
     */
    final public function exportSiteProject(): bool
    {
        if (!$this->exportImages) {
            $this->exceptions[] = '/htdocs/uploads';
        }

        $db = Yii::$app->getDb();
        if ($this->exportDb) {
            exec(
                'mysqldump --host=' . $this->getDsnAttribute('host', $db->dsn) .
                    ' --user=' . $db->username .
                    ' --password=' . $db->password . ' ' . $this->getDsnAttribute('dbname', $db->dsn) .
                    ' --skip-add-locks  > ' .
                    dirname(__DIR__, 2) . "/dumps/{$_SERVER['SERVER_NAME']}.sql"
            );
        }
        ExtendedZip::zipTree(
            dirname(__DIR__, 2),
            $this->filename,
            $this->exceptions,
            ZipArchive::CREATE | ZipArchive::OVERWRITE
        );
        return true;
    }

    private function getDsnAttribute(string $name, string $dsn): ?string
    {
        if (preg_match("/$name=([^;]*)/", $dsn, $match)) {
            return $match[1];
        }
        return null;
    }
}
