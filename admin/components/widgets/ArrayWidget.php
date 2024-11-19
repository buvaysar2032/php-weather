<?php

namespace admin\components\widgets;

use common\models\{AppActiveRecord, AppModel};
use Yii;
use yii\base\{InvalidCallException, InvalidConfigException, Model, Widget};
use yii\db\{ActiveRecord, ActiveRecordInterface};
use yii\helpers\Inflector;

/**
 * Виджет возвращающий конфигурацию для другого виджета
 *
 * @package admin\components\widgets
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
abstract class ArrayWidget extends Widget
{
    /**
     * Return the config array for another widget
     *
     * @param array $config           Конфигурация виджета
     * @param array $additionalConfig Дополнительные элементы выходного массива
     *
     * @throws InvalidConfigException
     */
    public static function widget($config = [], array $additionalConfig = []): array
    {
        $config['class'] = static::class;
        /** @var self $widget */
        $widget = Yii::createObject($config);
        return array_merge($widget->run(), $additionalConfig);
    }

    /**
     * {@inheritdoc}
     */
    public static function begin($config = []): static
    {
        throw new InvalidCallException(static::class . '::begin() is not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public static function end(): static
    {
        throw new InvalidCallException(static::class . '::end() is not implemented');
    }

    /**
     * Парсинг названия поля,
     *
     * Если связанные данные, то ожидаем формат аттрибута: 'userExt__phone'
     *
     * @param string           $attr     Название поля
     * @param bool|string|null $viewAttr Название второго поля для отображения зависимого значения
     */
    public static function _parseAttrValue(string $attr, bool|string $viewAttr = null): array
    {
        if ($viewAttr !== null) {
            if ($viewAttr === true) {
                $vars = self::_parseAttrValue($attr);
            } else {
                $vars = self::_parseAttrValue($viewAttr);
            }
            /**
             * @var string $valueAttr
             */
            extract($vars);
            $viewAttr = $valueAttr;
        }
        /**
         * @var string|array $valueAttr
         */
        $valueAttr = $attr;
        $isRelative = str_contains($attr, '.');
        $snakeAttr = Inflector::underscore($valueAttr);
        return compact('valueAttr', 'isRelative', 'snakeAttr', 'viewAttr');
    }

    /**
     * Executes the ArrayWidget.
     *
     * @return array the result of ArrayWidget execution to be outputted.
     */
    public function run(): array
    {
        return [];
    }

    /**
     * Получение уникального ID для JS поля в таблице
     */
    final protected function _getInputUniqueId(Model $model): string
    {
        $attr = str_replace('.', '_', $this->attr);
        if (!$model instanceof ActiveRecordInterface) {
            return $attr;
        }
        $tableName = '';
        if ($model instanceof ActiveRecord) {
            $tableName = str_replace(['{{%', '}}'], ['', ''], $model::tableName()) . '_';
        }
        $pKey = $model->getPrimaryKey();
        if (is_array($pKey)) {
            $pKey = implode('_', $pKey);
        }
        return "$tableName{$pKey}_$attr";
    }

    /**
     * Получить связанные данные, которые дополнительно проходят проверку экранированием
     */
    final protected function _getRelatedClassData(Model $data, ?string $attr): mixed
    {
        if (!$attr) {
            return null;
        }
        return $this->_formatData($data, $attr);
    }

    /**
     * Экранирование данных для вывода
     */
    private function _formatData(?Model $model, string $attribute): mixed
    {
        if (!$model) {
            return null;
        }
        if (!is_string($model->$attribute)) {
            return $model->$attribute;
        }

        $result = $model->$attribute;
        if ($model instanceof AppActiveRecord || $model instanceof AppModel) {
            if (!Yii::$app->has('screener')) {
                Yii::warning('`screener` module is missing');
                return $result;
            }
            $rules = Yii::$app->screener->rulesByKey($model->formatRules(), $attribute);
            $result = Yii::$app->screener->prepareText($result, $rules);
        }

        return $result;
    }
}
