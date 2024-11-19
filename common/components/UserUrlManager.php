<?php

namespace common\components;

use yii\web\UrlManager;

/**
 * Расширенная версия UrlManager
 *
 * @package common\components
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class UserUrlManager extends UrlManager
{
    /**
     * Нужно ли скрывать index из Url (true/false)
     */
    public bool $hideIndex = true;

    /**
     * {@inheritdoc}
     */
    final public function createUrl($params): string
    {
        $url = parent::createUrl($params);
        if ($this->hideIndex) {
            $url = $this->_removeIndex($url);
        }
        return $url;
    }

    /**
     * Убирает index из url
     */
    private function _removeIndex(string $url): string
    {
        if (str_contains($url, 'test')) {
            return $url;
        }
        return str_replace('/index', '', $url);
    }
}
