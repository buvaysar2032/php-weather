<?php

namespace common\components;

use common\components\exceptions\ModelSaveException;
use Faker\{Factory, Generator};
use yii\base\BaseObject;
use yii\db\ActiveRecord;

/**
 * Класс для динамического заполнения таблицы БД случайными данными на основе правил ActiveRecord
 *
 * @package common\components
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class Faker extends BaseObject
{
    /**
     * Локализация для генерируемых значений.
     */
    public string $locale = 'ru_RU';

    /**
     * Генератор случайных значений.
     */
    public Generator $generator;

    /**
     * {@inheritdoc}
     */
    final public function init(): void
    {
        parent::init();
        $this->generator = Factory::create($this->locale);
    }

    /**
     * Заполнение таблицы случайными данными
     *
     * Принимает массив параметров формата:
     * ```php
     *  [
     *      // Список предустановленных полей, которые не нужно выбирать случайно
     *      'rules' => [
     *          'status' => 10,
     *          'rules_accepted' => 1,
     *      ],
     *      // Объект модели, который надо подцепить внешним ключом (зацепиться по правилу exist)
     *      Model::class => $model,
     *  ];
     * ```
     *
     * @param string $modelClass Имя класса заполняемой модели
     * @param int    $count      Количество записей
     * @param array  $params     Массив параметров
     *
     * @return ActiveRecord[]
     * @throws ModelSaveException
     */
    final public function fill(string $modelClass, int $count = 1, array $params = []): array
    {
        $models = [];
        while ($count > 0) {
            /** @var ActiveRecord $model */
            $model = new $modelClass();
            $rules = $model->rules();
            foreach ($rules as $rule) {
                if ($rule[1] === 'required') {
                    continue;
                }
                $this->_parseModelRule($model, $rule, $params);
            }
            if (!$model->save()) {
                throw new ModelSaveException($model);
            }
            $model->refresh();
            $models[] = $model;
            $count--;
        }
        return $models;
    }

    /**
     * @param ActiveRecord $model  Заполняемая модель
     * @param array        $rule   Проверяемое правило валидации
     * @param array        $params Массив параметров
     *
     * @see Faker::fill
     */
    private function _parseModelRule(ActiveRecord $model, array $rule, array $params): void
    {
        if (is_array($rule[0])) {
            foreach ($rule[0] as $item) {
                // Запись предустановленных значений
                if ($params['rules'][$item] !== null) {
                    $model->$item = $params['rules'][$item];
                    continue;
                }
                $this->_generateFieldValue($model, $rule, $item, $params);
            }
        } else {
            // Запись предустановленных значений
            if ($params['rules'][$rule[0]] !== null) {
                $model->{$rule[0]} = $params['rules'][$rule[0]];
            }
            $this->_generateFieldValue($model, $rule, $rule[0], $params);
        }
    }

    /**
     * Сгенерировать значение для поля следуя правилам валидации и правилам из аргумента params
     *
     * @param ActiveRecord $model  Созданная модель
     * @param array        $rule   Правило валидации
     * @param string       $item   Имя поля
     * @param array        $params Массив параметров
     */
    private function _generateFieldValue(ActiveRecord $model, array $rule, string $item, array $params): void
    {
        // Запись случайного значения
        $value = match ($rule[1]) {
            'email' => $this->generator->email,
            'string' => $this->randomString($item, $rule['max'] ?: 220),
            'number', 'integer' => $this->randomInt($item),
            'exist' => $params[$rule['targetClass']]->id,
            default => null,
        };
        $model->$item = $value;
    }

    /**
     * Случайная строка.
     */
    private function randomString(string $item, int $maxNbChars = 200): ?string
    {
        return match ($item) {
            'username' => $this->generator->userName,
            'name' => $this->generator->name,
            'unconfirmed_email', 'email', 'e-mail' => $this->generator->email,
            'phone', 'phone_number', 'phoneNumber' => $this->generator->phoneNumber,
            'first_name', 'middle_name' => $this->generator->firstName,
            'last_name' => $this->generator->lastName,
            'color' => $this->generator->colorName,
            'domain' => $this->generator->domainName,
            default => $this->generator->text($maxNbChars),
        };
    }

    /**
     * Случайное целое число.
     */
    private function randomInt(string $item): ?int
    {
        if (in_array($item, ['created_at', 'updated_at', 'last_login_at'])) {
            $value = time();
        } else {
            $value = mt_rand();
        }
        return $value;
    }
}
