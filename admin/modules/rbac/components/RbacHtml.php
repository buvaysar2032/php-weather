<?php

namespace admin\modules\rbac\components;

use Exception;
use yii\bootstrap5\Html;

class RbacHtml extends Html
{
    use ActionFilterTrait;

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public static function a($text, $url = null, $options = [], bool $hideOnPermission = false): string
    {
        if (!self::isAvailable($url)) {
            if ($hideOnPermission) {
                return '';
            }
            $classes = $options['class'] ?? [];
            if (!is_array($classes)) {
                $classes = explode(' ', $classes);
            }
            if (in_array('btn', $classes, true)) {
                self::addCssClass($options, 'disabled');
            } else {
                self::addCssStyle($options, ['pointer-events' => 'none']);
            }
        }
        return parent::a($text, $url, $options);
    }
}