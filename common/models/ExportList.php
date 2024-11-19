<?php

namespace common\models;

use common\components\helpers\UserFileHelper;
use Exception;
use Yii;

/**
 * This is the model class for table "{{%export_films_list}}".
 *
 * @property int         $id
 * @property string      $filename
 * @property int         $date
 * @property int         $count
 * @property-read string $downloadLink
 * @property-read int    $filesize
 * @property-read string $downloadLabel
 */
class ExportList extends AppActiveRecord
{
    use traits\ObjectStorageTrait;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%export_list}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['filename', 'date'], 'required'],
            [['date', 'count'], 'integer'],
            ['filename', 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'filename' => Yii::t('app', 'Filename'),
            'date' => Yii::t('app', 'Date'),
            'count' => Yii::t('app', 'Count'),
            'downloadLink' => Yii::t('app', 'Download Link')
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes): void
    {
        if ($insert && self::hasS3Storage()) {
            $filename = Yii::getAlias('@root/admin/runtime/export/') . $this->filename;
            try {
                self::getS3Client()
                    ->upload(
                        Yii::$app->environment->S3_PRIVATE_BUCKET,
                        $this->filename,
                        fopen($filename, 'r')
                    );
            } catch (Exception $exception) {
                Yii::error($exception->getMessage(), __METHOD__);
                Yii::$app->session->addFlash('error', $exception->getMessage());
            }
            if (file_exists($filename)) {
                unlink($filename);
            }
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete(): void
    {
        if (self::hasS3Storage()) {
            try {
                if (self::getS3Client()->doesObjectExist(Yii::$app->environment->S3_PRIVATE_BUCKET, $this->filename)) {
                    self::getS3Client()->deleteObject([
                        'Bucket' => Yii::$app->environment->S3_PRIVATE_BUCKET,
                        'Key' => $this->filename
                    ]);
                }
            } catch (Exception $exception) {
                Yii::error($exception->getMessage(), __METHOD__);
                Yii::$app->session->addFlash('error', $exception->getMessage());
            }
        }
        $filename = Yii::getAlias('@root/admin/runtime/export/') . $this->filename;
        if (file_exists($filename)) {
            unlink($filename);
        }
        parent::afterDelete();
    }

    /**
     * Ссылка на скачивание
     */
    public function getDownloadLink(): string
    {
        return '/admin/export/download/' . $this->filename;
    }

    public function getFilesize(): int
    {
        if (self::hasS3Storage()) {
            try {
                if (
                    self::getS3Client()->doesObjectExist(Yii::$app->environment->S3_PRIVATE_BUCKET, $this->filename)
                    && ($size = (int)(self::getS3Client()->headObject([
                        'Bucket' => Yii::$app->environment->S3_PRIVATE_BUCKET,
                        'Key' => $this->filename
                    ])['ContentLength'] ?? 0))
                ) {
                    return $size;
                }
            } catch (Exception $exception) {
                Yii::error($exception->getMessage(), __METHOD__);
                Yii::$app->session->addFlash('error', $exception->getMessage());
            }
        }
        $filename = Yii::getAlias('@root/admin/runtime/export/') . $this->filename;
        if (file_exists($filename)) {
            return filesize($filename);
        }
        return 0;
    }

    public function getDownloadLabel(): string
    {
        return $this->filename . ' ' . UserFileHelper::bytesToString($this->filesize);
    }
}
