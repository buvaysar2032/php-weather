<?php

namespace common\enums;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\{ArrayHelper, Json};
use yii\validators\{ValidationAsset, Validator};

/**
 * Class EnumValidator
 *
 * @package common\enums
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class EnumValidator extends Validator
{
    /**
     * Dictionary enum classname
     */
    public DictionaryInterface|string $enum;
    /** Whether the comparison is strict (both type and value must be the same) */
    public bool $strict = false;
    /**
     * Whether to invert the validation logic. Defaults to false. If set to true,
     * the attribute value should NOT be among the list of values defined via [[range]].
     */
    public bool $not = false;
    /** Whether to allow array type attribute. */
    public bool $allowArray = false;

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        if (!isset($this->enum)) {
            throw new InvalidConfigException('The "enum" property must be set.');
        }
        if (!class_exists($this->enum)) {
            throw new InvalidConfigException("The enum `$this->enum` does not exists");
        }
        if (!in_array(DictionaryInterface::class, class_implements($this->enum), true)) {
            throw new InvalidConfigException("The enum `$this->enum` must implements " . DictionaryInterface::class);
        }
        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} is invalid.');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function validateValue($value): ?array
    {
        $in = false;

        if (
            $this->allowArray
            && is_iterable($value)
            && ArrayHelper::isSubset($value, $this->enum::values(), $this->strict)
        ) {
            $in = true;
        }

        if (!$in && ArrayHelper::isIn($value, $this->enum::values(), $this->strict)) {
            $in = true;
        }

        return $this->not !== $in ? null : [$this->message, []];
    }

    /**
     * {@inheritdoc}
     */
    public function clientValidateAttribute($model, $attribute, $view): ?string
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);

        return 'yii.validation.range(value, messages, ' . Json::htmlEncode($options) . ');';
    }

    /**
     * {@inheritdoc}
     */
    public function getClientOptions($model, $attribute): array
    {
        $range = [];
        foreach ($this->enum::values() as $value) {
            $range[] = (string)$value;
        }
        $options = [
            'range' => $range,
            'not' => $this->not,
            'message' => $this->formatMessage($this->message, ['attribute' => $model->getAttributeLabel($attribute)]),
        ];
        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }
        if ($this->allowArray) {
            $options['allowArray'] = 1;
        }

        return $options;
    }
}
