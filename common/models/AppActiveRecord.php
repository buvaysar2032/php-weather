<?php

namespace common\models;

use common\components\behaviors\UserFiltersBehavior;
use common\modules\log\behaviors\Logger;
use Yii;
use yii\base\{BaseObject, InvalidConfigException};
use yii\db\{ActiveQuery, ActiveRecord};
use yii\helpers\{ArrayHelper, Inflector};

/**
 * Расширенный базовый класс ActiveRecord
 *
 * Предоставляет возможность поиска точек по координатному spatial полю в радиусе
 *
 * @package models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property array           $synchronizeAttributes Список синхронизируемых атрибутов с внешними сущностями
 * @property-read string|int $primaryId
 */
abstract class AppActiveRecord extends ActiveRecord
{
    use traits\SetTypedAttributesTrait;
    use traits\LoadEventsTrait;
    use traits\ScreenerTrait;
    use traits\SelectMultipleField;

    /**
     * {@inheritdoc}
     * @return array<array<string|int|callable>>
     */
    public function rules(): array
    {
        return parent::rules();
    }

    /**
     * {@inheritdoc}
     */
    public function attributes(): array
    {
        return array_merge(parent::attributes(), static::externalAttributes());
    }

    /**
     * Список внешних атрибутов
     */
    public static function externalAttributes(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function find(): AppActiveQuery
    {
        return new AppActiveQuery(static::class);
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        $value = parent::__get($name);
        if (
            is_null($value)
            && in_array($name, $this->attributes(), true)
            && !str_ends_with(static::class, 'Search')
            && str_contains($name, '.')
        ) {
            $data = explode('.', $name);
            $method = 'get' . ucfirst($data[0]);
            if (method_exists($this, $method) && $this->{$method}() instanceof ActiveQuery) {
                return $this->$name = $this->{$data[0]}?->{$data[1]};
            }
        }
        return $value;
    }

    private static array $extAttrs = [];

    /**
     * Получение названия поля и составление joinWith массива
     *
     * @param string $attribute Название поля
     * @param array  $select    Массив для метода select
     * @param array  $joinWith  Массив для метода joinWith
     */
    public static function parseQueryField(
        string $attribute,
        array &$select,
        array &$joinWith = []
    ): void {
        if (str_contains($attribute, '.')) {
            $extModelData = explode('.', $attribute);
            $extAttribute = $extModelData[1];
            if (!isset(self::$extAttrs[$attribute])) {
                $method = 'get' . ucfirst($extModelData[0]);
                if (method_exists(new static(), $method)) {
                    $query = (new static())->{$method}();
                    if ($query instanceof ActiveQuery) {
                        $extModelName = $query->modelClass::tableName();
                        if (!(new $query->modelClass())->hasAttribute($extAttribute)) {
                            return;
                        }
                        self::$extAttrs[$attribute] = $extModelName;
                    }
                }
            } else {
                $extModelName = self::$extAttrs[$attribute];
            }
            if (!isset($extModelName)) {
                $extModelName = '{{%' . Inflector::camel2id($extModelData[0], '_') . '}}';
            }
            if (!in_array($extModelData[0], $joinWith, true)) {
                $joinWith[] = $extModelData[0];
            }
            $select[] = "$extModelName.`$extAttribute`" . ($attribute !== $extAttribute ? " AS `$attribute`" : null);
        } else {
            $select[] = static::tableName() . '.' . $attribute;
        }
    }

    /**
     * {@inheritdoc}
     *
     * Заполнение пустых внешних аттрибутов
     */
    public function afterFind(): void
    {
        parent::afterFind();
        $this->findExternalAttributes();
    }

    private function findExternalAttributes(): void
    {
        foreach ($this->attributes() as $attribute) {
            if (str_contains($attribute, '.')) {
                $parts = explode('.', $attribute);
                $value = $this;
                while ($value instanceof BaseObject) {
                    $part = array_shift($parts);
                    $value = $value->$part;
                }
                $this->setAttribute($attribute, $value);
            }
        }
    }

    public function getPrimaryId(): int|string
    {
        $key = $this->getPrimaryKey();
        if (is_array($key)) {
            $key = array_shift($key);
        }
        if (is_numeric($key)) {
            $key = (int)$key;
        } else {
            $key = (string)$key;
        }
        return $key;
    }

    /**
     * Creates and populates a set of models.
     *
     * @return static[]
     * @throws InvalidConfigException
     */
    public static function createMultiple(array $multipleModels = []): array
    {
        $formName = (new static())->formName();
        $post = Yii::$app->request->post($formName);
        $models = [];

        if (!empty($multipleModels)) {
            $keys = array_keys(ArrayHelper::map($multipleModels, 'id', 'id'));
            $multipleModels = array_combine($keys, $multipleModels);
        }

        if ($post && is_array($post)) {
            foreach ($post as $item) {
                if (isset($item['id'], $multipleModels[$item['id']])) {
                    $models[] = $multipleModels[$item['id']];
                } else {
                    $models[] = new static();
                }
            }
        }

        unset($model, $formName, $post);

        return $models;
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'logger' => ['class' => Logger::class],
            'userFilters' => ['class' => UserFiltersBehavior::class]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getDirtyAttributes($names = null): array
    {
        if (is_null($names) && ($externalAttributes = static::externalAttributes())) {
            $names = array_diff($this->attributes(), $externalAttributes);
        }
        return parent::getDirtyAttributes($names);
    }
}
