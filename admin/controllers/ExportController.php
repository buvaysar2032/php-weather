<?php

namespace admin\controllers;

use common\models\ExportList;
use Exception;
use Yii;
use yii\helpers\FileHelper;
use yii\web\NotFoundHttpException;

/**
 * Class ExportController
 *
 * @package admin\controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class ExportController extends AdminController
{
    /**
     * @throws NotFoundHttpException
     */
    public function actionDownload(string $filename): void
    {
        $path = Yii::getAlias('@root/admin/runtime/export');
        $file = $path . '/' . $filename;
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $contentType = match ($extension) {
            'csv' => 'application/csv',
            default => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        };
        try {
            if (
                ExportList::hasS3Storage()
                && ExportList::getS3Client()->doesObjectExist(Yii::$app->environment->S3_PRIVATE_BUCKET, $filename)
            ) {
                FileHelper::createDirectory($path);
                ExportList::getS3Client()->getObject([
                    'Bucket' => Yii::$app->environment->S3_PRIVATE_BUCKET,
                    'Key' => $filename,
                    'SaveAs' => $file,
                ]);
            }
        } catch (Exception $exception) {
            Yii::$app->session->addFlash('error', $exception->getMessage());
            Yii::error($exception->getMessage(), __METHOD__);
        }
        if (file_exists($file)) {
            header('Cache-Control: public, must-revalidate, max-age=0');
            header('Pragma: public');
            header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header("Content-Type: $contentType; charset=utf-8");
            header("Content-Disposition: attachment; filename=\"$filename\"");
            readfile($file);
            exit();
        }
        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
