<?php

namespace common\modules\notification\widgets;

use admin\modules\rbac\components\ActionFilterTrait;
use common\modules\notification\models\Notification as NotificationModel;
use common\widgets\VueWidget;
use Exception;
use yii\bootstrap5\Html;
use yii\helpers\{Json, Url};

/**
 * Class Notification
 *
 * @package notification
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class NotificationBell extends VueWidget
{
    use ActionFilterTrait;

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function run(): string
    {
        $url = '/notification/default';
        if (!self::isAvailable($url)) {
            return '';
        }
        /** @var NotificationModel[] $notifications */
        $notifications = NotificationModel::find()
            ->orderBy(['updated_at' => SORT_DESC])
            ->all();
        foreach ($notifications as &$notification) {
            $notification = $notification->toArray();
        }
        unset($notification);
        return Html::tag('notification-bell', null, [
            ':notifications' => Json::htmlEncode($notifications),
            'url-module' => Url::toRoute($url)
        ]);
    }
}