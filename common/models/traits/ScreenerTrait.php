<?php

namespace common\models\traits;

use Yii;

/**
 * Trait ScreenerTrait
 *
 * @package common\models\traits
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
trait ScreenerTrait
{
    /**
     * {@inheritdoc}
     */
    public function toArray(array $fields = [], array $expand = [], $recursive = true): array
    {
        $data = parent::toArray($fields, $expand, $recursive);
        if (!Yii::$app->has('screener')) {
            Yii::warning('`screener` module is missing');
            return $data;
        }
        foreach ($data as $key => &$value) {
            $rules = Yii::$app->screener->rulesByKey($this->formatRules(), $key);
            $value = Yii::$app->screener->prepareText($value, $rules);
        }
        return $data;
    }

    /**
     * Массив правил подготовки текста.
     *
     * Позволяет сменить способ форматирования, которые экранируют опасные значения
     */
    public function formatRules(): array
    {
        return [];
    }
}
