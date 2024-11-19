<?php

namespace common\components\helpers;

use common\models\AppActiveRecord;
use Generator;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\{Query, QueryInterface};
use yii\data\Sort;

/**
 * Класс SearchWidgetHelper
 *
 * Позволяет быстро построить запрос в search модели с поиском и фильтрацией вложенных данных
 *
 * @package admin\components\widgets
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class SearchQueryHelper
{
    //>>> Сортировка
    /**
     * Обеспечение сортировки
     *
     * @param array|string    $fields Массив полей либо одно поле
     * @param AppActiveRecord $model  Модель
     * @param QueryInterface  $query  Запрос
     *
     * @return ActiveDataProvider Поставщик данных с параметрами сортировки
     */
    public static function sortableDataProvider(
        array|string $fields,
        AppActiveRecord $model,
        QueryInterface $query
    ): ActiveDataProvider {
        $dataProvider = new ActiveDataProvider(['query' => $query]);
        $sort = new Sort(['attributes' => self::setAttributesSort($fields, $model)]);
        $dataProvider->setSort($sort);
        return $dataProvider;
    }

    /**
     * Установка параметров сортировки
     *
     * @param array|string    $fields Массив полей либо одно поле
     * @param AppActiveRecord $model  Модель
     */
    private static function setAttributesSort(array|string $fields, AppActiveRecord $model): array
    {
        $sort = [];
        foreach (self::fieldsIterator($fields, $model) as $field => $attribute) {
            $sort[$attribute] = [
                'asc' => [$field => SORT_ASC],
                'desc' => [$field => SORT_DESC],
                'default' => SORT_ASC
            ];
        }
        self::refreshSort($sort);
        return $sort;
    }

    /**
     * Очистка параметров сортировки
     *
     * @param array $sort Массив с параметрами сортировки
     */
    private static function refreshSort(array &$sort): void
    {
        foreach ($sort as $key => $item) {
            if (is_int($key) && is_string($item)) {
                unset($sort[$key]);
                $sort[$item] = $item;
            }
        }
    }

    //>>> Поиск

    /**
     * Поиск по простым сравнениям
     *
     * @param AppActiveRecord $model  Модель
     * @param array|string    $fields Массив полей либо одно поле
     * @param QueryInterface  $query  Запрос
     */
    public static function filterSimpleSearch(
        array|string $fields,
        AppActiveRecord $model,
        QueryInterface $query
    ): void {
        foreach (self::fieldsIterator($fields, $model) as $field => $attribute) {
            self::addSimpleSearch($attribute, $field, $model, $query);
        }
    }

    private static function addSimpleSearch(
        string $attribute,
        string $field,
        AppActiveRecord $model,
        QueryInterface $query
    ): void {
        if (null !== ($search = $model->$attribute)) {
            $attributeName = self::getDBAttributeName($field, $model);
            $query->andFilterWhere([$attributeName => $search]);
        }
    }

    /**
     * Поиск по дате в модели
     *
     * @param AppActiveRecord $model  Модель
     * @param array|string    $fields Массив полей либо одно поле
     * @param QueryInterface  $query  Запрос
     */
    public static function filterDataRange(array|string $fields, AppActiveRecord $model, QueryInterface $query): void
    {
        foreach (self::fieldsIterator($fields, $model) as $field => $attribute) {
            self::addDatetimeSearch($attribute, $field, $model, $query);
        }
    }

    /**
     * Поиск по дате по аттрибуту
     *
     * @param string          $field Название поля
     * @param AppActiveRecord $model Модель
     * @param QueryInterface  $query Запрос
     */
    private static function addDatetimeSearch(
        string $attribute,
        string $field,
        AppActiveRecord $model,
        QueryInterface $query
    ): void {
        $search = (string)$model->$attribute;
        $attributeName = self::getDBAttributeName($field, $model);
        $divider = ' - ';
        $div_pos = strpos($search, $divider);
        if (!$div_pos) {
            $query->andFilterWhere([$attributeName => $search]);
        } else {
            $regExp = '/(\d{2}\.\d{2}\.\d{4})\s?(\d{2}:\d{2}(:\d{2})?)?/';
            $from = substr($search, 0, $div_pos);
            preg_match($regExp, $from, $matchesFrom);
            $from = strtotime($from);
            if (!$matchesFrom[2]) {
                $from = strtotime('today', $from);
            }
            $to = substr($search, $div_pos + 3);
            preg_match($regExp, $to, $matchesTo);
            $to = strtotime($to);
            if (!$matchesTo[2]) {
                $to = strtotime('tomorrow', $to) - 1;
            }

            $query->andFilterWhere(['>=', $attributeName, $from]);
            $query->andFilterWhere(['<=', $attributeName, $to]);
        }
    }

    /**
     * Поиск по диапазонам и сравнениям
     *
     * @param array|string    $fields Массив полей либо одно поле
     * @param AppActiveRecord $model  Модель
     * @param QueryInterface  $query  Запрос
     */
    public static function filterIntegerRange(array|string $fields, AppActiveRecord $model, QueryInterface $query): void
    {
        foreach (self::fieldsIterator($fields, $model) as $field => $attribute) {
            self::addIntegerRangeSearch($attribute, $field, $model, $query);
        }
    }

    /**
     * Поиск по диапазону и сравнению
     */
    private static function addIntegerRangeSearch(
        string $attribute,
        string $field,
        AppActiveRecord $model,
        QueryInterface $query
    ): void {
        if (null !== ($search = $model->$attribute)) {
            $attributeName = self::getDBAttributeName($field, $model);
            $divider = ' - ';
            $div_pos = strpos($search, $divider);
            if (!$div_pos) {
                $comparators = ['<=', '>=', '<', '>'];
                $compared = false;
                foreach ($comparators as $comparator) {
                    if (false !== ($comparator_pos = strpos($search, $comparator))) {
                        $query->andFilterWhere(
                            [
                                $comparator,
                                $attributeName,
                                trim(substr($search, $comparator_pos + strlen($comparator)))
                            ]
                        );
                        $compared = true;
                        break;
                    }
                }
                if (!$compared) {
                    $query->andFilterWhere([$attributeName => $search]);
                }
            } else {
                $from = substr($search, 0, $div_pos);
                $to = substr($search, $div_pos + 3);
                $query->andFilterWhere(['>=', $attributeName, $from]);
                $query->andFilterWhere(['<=', $attributeName, $to]);
            }
        }
    }

    /**
     * Поиск по похожим значениям модели
     *
     * @param array|string    $fields Массив полей либо одно поле
     * @param AppActiveRecord $model  Модель
     * @param QueryInterface  $query  Запрос
     */
    public static function filterLikeString(
        array|string $fields,
        AppActiveRecord $model,
        QueryInterface $query
    ): void {
        foreach (self::fieldsIterator($fields, $model) as $field => $attribute) {
            self::addAttributeLikeSearch($attribute, $field, $model, $query);
        }
    }

    /**
     * Поиск по похожим значениям аттрибута
     *
     * @param string          $field Название поля
     * @param AppActiveRecord $model Модель
     * @param QueryInterface  $query Запрос
     */
    private static function addAttributeLikeSearch(
        string $attribute,
        string $field,
        AppActiveRecord $model,
        QueryInterface $query
    ): void {
        $attributeName = self::getDBAttributeName($field, $model);
        $query->andFilterWhere(['like', $attributeName, $model->$attribute]);
    }

    private static function fieldsIterator(array|string $fields, AppActiveRecord $model): Generator
    {
        if (is_array($fields)) {
            foreach ($fields as $field) {
                $arr = self::fieldToAttrValue($field, $model);
                yield $arr['field'] => $arr['attribute'];
            }
        } elseif (is_string($fields)) {
            $arr = self::fieldToAttrValue($fields, $model);
            yield $arr['field'] => $arr['attribute'];
        }
    }

    private static function fieldToAttrValue(string $field, AppActiveRecord $model): array
    {
        $attribute = $field;
        if (str_contains($field, '.')) {
            $parts = explode('.', $field);
            $attrName = array_pop($parts);
            $relation = array_shift($parts);
            if ($model->hasMethod('get' . ucfirst($relation))) {
                $extTable = $model->{'get' . ucfirst($relation)}()->modelClass::tableName();
                $field = "$extTable.$attrName";
            }
        }
        return compact('field', 'attribute');
    }

    /**
     * Получение имени аттрибута в БД
     *
     * @param string $field Название поля
     *
     * @return string Название аттрибута БД
     */
    private static function getDBAttributeName(string $field, AppActiveRecord $model): string
    {
        if (str_contains($field, '.')) {
            // Если это просто прописанное поле для построителя запросов
            $attributeName = $field;
        } elseif ($model->hasProperty($field, false)) {
            // Это поле из таблицы БД
            $attributeName = $model::tableName() . '.' . $field;
        } else {
            // Это любое другое поле, которое есть в SQL запросе
            $attributeName = $field;
        }
        return $attributeName;
    }

    /**
     * Динамический поиск числа с вводом правила поиска
     *
     * Возможные правила поиска: `>=`, `>`, `<=`, `<`, `!=`
     * Если правило не указать, то им будет считаться `=`
     *
     * @param Model  $model     Модель
     * @param string $attribute Название атрибута с safe правилом валидации
     * @param Query  $query     Строящийся запрос
     */
    public static function conditionSearch(Model $model, string $attribute, QueryInterface $query): void
    {
        $search = $model->$attribute;
        //                        1   2   3    4    5  [6][2]
        preg_match_all('~(>=)?(>)?(<=)?(<)?(!=)?(.*?)~', $search, $match);
        $val = $match[6][2];
        if ($match[1][0]) { // >=
            $query->andFilterWhere(['>=', $attribute, $val]);
        } elseif ($match[2][0]) { // >
            $query->andFilterWhere(['>', $attribute, $val]);
        } elseif ($match[3][0]) { // <=
            $query->andFilterWhere(['<=', $attribute, $val]);
        } elseif ($match[4][0]) { // <
            $query->andFilterWhere(['<', $attribute, $val]);
        } elseif ($match[5][0]) { // !=
            $query->andFilterWhere(['!=', $attribute, $val]);
        } else {
            $query->andFilterWhere([$attribute => $search]);
        }
    }
}
