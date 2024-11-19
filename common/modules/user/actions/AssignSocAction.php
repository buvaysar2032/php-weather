<?php

namespace common\modules\user\actions;

use Throwable;
use Yii;
use yii\base\{Exception, InvalidConfigException};
use yii\db\StaleObjectException;
use yii\web\{Response, UnauthorizedHttpException};

/**
 * Привязка соц. сети к уже созданному профилю
 *
 * @package common\modules\user\actions
 */
class AssignSocAction extends BaseAction
{
    /**
     * @return Response|string
     * @throws Throwable
     * @throws Exception
     * @throws InvalidConfigException
     * @throws StaleObjectException
     */
    public function run(): Response|string
    {
        if (Yii::$app->user->isGuest) {
            throw new UnauthorizedHttpException();
        }
        return $this->socAuth('assign');
    }
}