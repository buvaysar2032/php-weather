<?php

namespace common\components\helpers;

use Yii;
use yii\base\Module;

/**
 * Class ModuleHelper
 *
 * @package common\components
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class ModuleHelper
{
    public const ADMIN = 'admin';
    public const API = 'api';
    public const FRONTEND = 'frontend';
    public const CONSOLE = 'console';

    public static function isAdminModule(): bool
    {
        return Yii::$app->id === self::ADMIN;
    }

    public static function isApiModule(): bool
    {
        return Yii::$app->id === self::API;
    }

    public static function isFrontendModule(): bool
    {
        return Yii::$app->id === self::FRONTEND;
    }

    public static function isConsoleModule(): bool
    {
        return Yii::$app->id === self::CONSOLE;
    }
}
