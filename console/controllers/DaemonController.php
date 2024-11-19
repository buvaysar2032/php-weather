<?php

namespace console\controllers;

use common\modules\backup\models\DbWrap;
use Yii;
use yii\base\Exception;
use yii\console\ExitCode;
use yii\db\Exception as DbException;
use yii\helpers\BaseConsole;

/**
 * Контроллер для вызовов крона
 *
 * @package console\controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class DaemonController extends ConsoleController
{
    /**
     * Очистка от старых бэкапов.
     * Запуск по крону:
     * ```
     * * * * * * php yii daemon/clear-backups
     * ```
     *
     * @throws Exception
     */
    public function actionClearBackups(): int
    {
        $backups = DbWrap::getBackups();
        /** Время просрочки бекапа */
        $expireTime = 60 * 60 * 20;
        $currentTime = time();
        foreach ($backups as $backup) {
            // Поиск даты и времени создания в названии бекапа
            preg_match('/(\d{4}-\d{1,2}-\d{1,2})_(\d{1,2}-\d{1,2}-\d{1,2})/', $backup, $match);
            if ($match) {
                $date = Yii::$app->formatter->asTimestamp(
                    $match[1] . ' ' . str_replace('-', ':', $match[2]) . ' ' .
                    Yii::$app->formatter->timeZone
                );
                if (($currentTime - $date) > $expireTime) {
                    DbWrap::remove($match[0]); // Удаление просроченного бекапа
                    $this->stdout($backup . ' was deleted' . PHP_EOL, BaseConsole::FG_GREEN);
                }
            }
        }
        return ExitCode::OK;
    }

    /**
     * Резервное копирование БД.
     * Запуск по крону:
     * ```
     * * * * * * php yii daemon/backup
     * ```
     *
     * @throws Exception
     * @throws DbException
     */
    public function actionBackup(): int
    {
        DbWrap::exportDB();
        return ExitCode::OK;
    }
}
