<?php

namespace common\components\behaviors;

use common\models\AppActiveRecord;
use yii\base\{Behavior, InvalidConfigException};
use yii\db\{ActiveRecord, BaseActiveRecord};
use yii\helpers\Inflector;

/**
 * Поведение для генерации уникального идентификатора для ссылки
 *
 * @package common\components
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 * @property AppActiveRecord $owner
 */
class SlugifyBehavior extends Behavior
{
    /**
     * Список атрибутов из которых будет складываться идентификатор
     */
    public array $sourceAttributes = [];

    /**
     * Атрибут для хранения сгенерированного идентификатора
     */
    public ?string $slugAttribute;

    /**
     * Форсировать ли генерацию после каждого сохранения
     */
    public bool $forceGenerate = false;

    private BaseActiveRecord|string $_ownerClass;

    private array $_primaryKeys;

    /**
     * {@inheritdoc}
     */
    final public function events(): array
    {
        return [
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'slugify',
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'slugify'
        ];
    }

    /**
     * Генерация уникального идентификатора если поле пустое
     *
     * @throws InvalidConfigException
     */
    final public function slugify(): void
    {
        $this->checkOwner();
        if (!$this->forceGenerate && !empty($this->owner->{$this->slugAttribute})) {
            return;
        }
        $base = [];
        foreach ($this->sourceAttributes as $sourceAttribute) {
            $base[] = $this->owner->$sourceAttribute;
        }
        $variant = $slugBase = Inflector::slug(implode('-', $base));
        $suffix = 2;
        while (!$this->isUniqueSlug($variant)) {
            $variant = $slugBase . '-' . $suffix;
            $suffix++;
        }
        $this->owner->{$this->slugAttribute} = $variant;
    }

    /**
     * @throws InvalidConfigException
     */
    private function checkOwner(): void
    {
        /** @var ActiveRecord|string $class */
        $this->_ownerClass = get_class($this->owner);
        $this->_primaryKeys = $this->owner->getPrimaryKey(true);
        if (empty($this->slugAttribute)) {
            throw new InvalidConfigException('`slugAttribute` must be set');
        }
        if (!$this->owner->hasProperty($this->slugAttribute)) {
            throw new InvalidConfigException(
                'Unknown attribute `' . $this->slugAttribute . '` in model ' . get_class($this->owner)
            );
        }
        if (empty($this->sourceAttributes)) {
            throw new InvalidConfigException('`sourceAttributes` list is empty');
        }
        foreach ($this->sourceAttributes as $sourceAttribute) {
            if (!$this->owner->hasProperty($sourceAttribute)) {
                throw new InvalidConfigException(
                    'Unknown attribute `' . $sourceAttribute . '` in model ' . get_class($this->owner)
                );
            }
        }
    }

    /**
     * Проверка уникальности текущего варианта идентификатора.
     */
    private function isUniqueSlug(string $variant): bool
    {
        $query = $this->_ownerClass::find()->where([$this->slugAttribute => $variant]);
        foreach ($this->_primaryKeys as $primaryKey => $value) {
            $query->andFilterWhere(['!=', $primaryKey, $value]);
        }
        return !$query->exists();
    }
}
