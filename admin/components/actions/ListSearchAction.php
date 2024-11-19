<?php

namespace admin\components\actions;

use admin\components\widgets\ArrayWidget;
use Closure;
use common\modules\user\models\User;
use Yii;
use yii\base\{Action, InvalidConfigException};
use yii\db\{ActiveQuery, ActiveRecord, Exception};
use yii\helpers\Json;
use yii\web\Response;

/**
 * Class ListSearchAction
 * By default, searches for User models by `username` field
 *
 * @see     User
 * @package admin\components\actions
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class ListSearchAction extends Action
{
    /**
     * Имя класса модели для поиска
     */
    public ActiveRecord|string $modelClass = User::class;

    /**
     * Название поля модели по которому идет поиск
     */
    public string|array $searchFields = 'username';

    public bool $concat = true;

    /**
     * По какому полю брать индекс
     */
    public string $idAttr = 'id';

    public array $additionalSelectFields = [];

    /**
     * @var Closure|null a PHP callable that will be called right before executing of sql query
     *     ```php
     *     function (Query $query) {
     *     // $query is an object of select query
     *     }
     *     ```
     *     function must return query execution result
     */
    public ?Closure $additionalQuery;

    /**
     * @param string|null $q  Искомая строка
     * @param string|null $id Искомый id
     *
     * @throws InvalidConfigException
     * @throws Exception
     */
    final public function run(string $q = null, string $id = null): array
    {
        $query = $this->modelClass::find();
        if (!$this->searchFields) {
            throw new InvalidConfigException('`searchFields` property must be set');
        }
        if (is_array($this->searchFields)) {
            $snakeAttrs = [];
            foreach ($this->searchFields as $searchField) {
                $snakeAttrs[] = $this->addSearchField($query, $searchField);
            }
            if ($this->concat) {
                $snakeAttr = 'CONCAT(' . implode(', " ", ', $snakeAttrs) . ')';
            } else {
                $snakeAttr = array_shift($snakeAttrs);
                foreach ($snakeAttrs as &$attr) {
                    $parts = explode('.', $attr);
                    $attr .= ' AS dsp_' . array_pop($parts);
                }
                unset($attr);
                $this->additionalSelectFields = array_merge($this->additionalSelectFields, $snakeAttrs);
            }
        } else {
            $snakeAttr = $this->addSearchField($query, $this->searchFields);
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        $tableName = $this->modelClass::tableName();
        $query->select(
            array_merge(
                ["$tableName.`$this->idAttr` AS id", "$snakeAttr AS text"],
                $this->additionalSelectFields
            )
        )
            ->from($this->modelClass::tableName());

        if (!empty($q)) {
            if (is_array($this->searchFields)) {
                foreach ($this->searchFields as $searchField) {
                    $arr = ArrayWidget::_parseAttrValue($searchField);
                    extract($arr);
                    $query->orWhere(['like', $snakeAttr, $q])->limit(20);
                }
            } else {
                $query->where(['like', $snakeAttr, $q])->limit(20);
            }
        } elseif (!empty($id) && $id !== '[]') {
            if (!is_numeric($id) && preg_match('/^[[{].*[]}]$/', $id)) {
                $ids = Json::decode($id);
            } else {
                $ids = $id;
            }
            $query->where([$this->idAttr => $ids]);
        }
        if (isset($this->additionalQuery) && is_callable($this->additionalQuery)) {
            $function = $this->additionalQuery;
            $data = $function($query);
        } else {
            $command = $query->createCommand();
            $data = $command->queryAll();
        }
        $out['results'] = array_values($data);
        foreach ($out['results'] as &$result) {
            if (is_numeric($result['id'])) {
                $result['id'] = (int)$result['id'];
            }
        }
        return $out;
    }

    private function addSearchField(ActiveQuery $query, $field): string
    {
        $arr = ArrayWidget::_parseAttrValue($field);
        /**
         * Extracted variables
         *
         * @var string $valueAttr
         * @var bool   $isRelative
         * @var string $snakeAttr
         */
        extract($arr);
        if ($isRelative) {
            $extModel = explode('.', $valueAttr);
            $query->joinWith($extModel[0]); // Для большей вложенности создавать связи в модели через `via()`
        }
        return $snakeAttr;
    }
}
