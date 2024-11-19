<?php

namespace common\modules\notification;

use yii\base\Module;

/**
 * Notification module definition class
 *
 * Для создания уведомления просто вызвать
 * ```php
 * use common\modules\notification\models\Notification;
 *
 * Notification::create('success', 'Hello World!');
 * ```
 *
 * @package notification
 */
class Notification extends Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'common\modules\notification\controllers';
}
