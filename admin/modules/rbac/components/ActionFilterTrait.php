<?php

namespace admin\modules\rbac\components;

use admin\modules\rbac\filters\AccessControl;
use Exception;
use Yii;
use yii\base\{Action, Module};
use yii\di\Instance;
use yii\helpers\Url;
use yii\web\Controller;

trait ActionFilterTrait
{
    /**
     * @throws Exception
     */
    public static function checkNavItems(array &$items): void
    {
        foreach ($items as &$item) {
            if (!is_array($item)) {
                continue;
            }
            if (!empty($item['url']) && !self::isAvailable($item['url'])) {
                $item['visible'] = false;
            }
            if (!empty($item['items'])) {
                self::checkNavItems($item['items']);
            }
        }
        unset($item);
        $items = array_values($items);
    }

    /**
     * @throws Exception
     */
    public static function isAvailable(string|array $url): bool
    {
        $url = self::normalizeRoute($url);
        $parts = explode('/', ltrim($url, '/'));
        if (count($parts) >= 3) {
            while (count($parts) >= 3) {
                $module = new Module(array_shift($parts), $module ?? Yii::$app);
            }
        }
        $action = new Action(array_pop($parts), new Controller(array_pop($parts), $module ?? Yii::$app));
        if (!array_key_exists('access', Yii::$app->behaviors)) {
            return true;
        }
        /** @var AccessControl $accessControl */
        $accessControl = Instance::ensure(Yii::$app->behaviors['access'], AccessControl::class);
        if (!$accessControl->isActive($action)) {
            return true;
        }

        return $accessControl->isAllowed($action);
    }

    public static function normalizeRoute(string|array $url): string
    {
        $hideIndex = Yii::$app->urlManager->hideIndex;
        Yii::$app->urlManager->hideIndex = false;
        $res = str_replace(Yii::$app->request->baseUrl, '', Url::toRoute($url));
        Yii::$app->urlManager->hideIndex = $hideIndex;
        $res = preg_replace('/#.*$/', '', $res);
        return preg_replace('/\?.*$/', '', $res);
    }
}