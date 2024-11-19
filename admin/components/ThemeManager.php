<?php

namespace admin\components;

use admin\modules\rbac\components\RbacHtml;
use Exception;
use kartik\icons\Icon;
use Yii;
use yii\base\{Component, InvalidConfigException};
use yii\web\Cookie;

/**
 * Class ThemeManager
 *
 * @package admin\components
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property-read bool $isDark
 */
class ThemeManager extends Component
{
    public function getIsDark(): bool
    {
        return Yii::$app->request->cookies->get('theme')?->value === 'dark';
    }

    public function switchTheme(): void
    {
        $cookie = new Cookie(['name' => 'theme', 'value' => 'dark', 'expire' => time() + 31536000]);
        if (!$this->isDark) {
            Yii::$app->response->cookies->add($cookie);
        } else {
            Yii::$app->response->cookies->remove($cookie);
        }
    }

    /**
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function renderSwitchButton(): string
    {
        return RbacHtml::a(Icon::show($this->isDark ? 'sun' : 'moon'), ['/site/switch-theme'], [
            'title' => 'Переключиться на ' . ($this->isDark ? 'светлую' : 'темную') . " тему",
            'data-bs-toggle' => 'tooltip'
        ]);
    }
}
