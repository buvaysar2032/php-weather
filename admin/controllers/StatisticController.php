<?php

namespace admin\controllers;

use admin\enums\AdminStatus;
use admin\models\UserAdmin;
use admin\widgets\apexCharts\Line;
use admin\widgets\tooltip\TooltipWidget;
use common\components\helpers\ChartHelper;
use common\enums\Boolean;
use common\modules\backup\models\DbWrap;
use common\modules\mail\{enums\LogStatus, models\MailingLog};
use common\modules\user\models\{Email, User};
use Throwable;
use Yii;
use yii\base\Exception;
use yii\web\Response;

/**
 * Контроллер раздела статистики
 *
 * @package controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class StatisticController extends AdminController
{
    /**
     * Страница статистики
     *
     * @throws Exception|Throwable
     */
    public function actionIndex(string $id = null): array|string
    {
        switch ($id) {
            case 'users-registered':
                $line = new Line(['name' => 'Число зарегистрированных пользователей']);
                $startTime = strtotime(
                    'this monday',
                    strtotime('-1 year')
                );
                foreach (
                    ChartHelper::periodIterator($startTime, time(), '+1 week')
                    as $date => [$currentTime, $currentEndTime, $cache]
                ) {
                    $line->addPoint(
                        (int)User::find()
                            ->where(['>=', 'created_at', $currentTime])
                            ->andWhere(['<=', 'created_at', $currentEndTime])
                            ->count(),
                        $date
                    );
                }
                return $this->returnSeries([$line]);
            default:
                break;
        }

        $data = [];
        $data[] = [
            'name' => 'Всего пользователей',
            'value' => User::find()->count()
        ];
        $data[] = [
            'name' => 'Подтвержден Email ' . TooltipWidget::widget(
                    ['title' => Yii::t('app', 'The number of users who are fully registered')]
                ),
            'value' => Email::find()->where(['is_confirmed' => Boolean::Yes->value])->count()
        ];
        $data[] = [
            'name' => 'Кол-во активных бекапов',
            'value' => count(DbWrap::getBackups())
        ];
        $data[] = [
            'name' => 'Кол-во ошибок отправки писем',
            'value' => MailingLog::find()->where(['status' => LogStatus::Error->value])->count()
        ];
        $data[] = [
            'name' => 'Общее кол-во администраторов',
            'value' => UserAdmin::find()->count()
        ];
        $data[] = [
            'name' => 'Кол-во активных администраторов',
            'value' => UserAdmin::find()->where(['status' => AdminStatus::Active->value])->count()
        ];

        return $this->render('index', ['data' => $data]);
    }

    /**
     * @param Line[] $lines
     */
    private function returnSeries(array $lines): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $series = [];
        foreach ($lines as $line) {
            $series[] = ['name' => $line->name, 'data' => $line->sortedData];
        }
        return ['series' => $series];
    }
}
