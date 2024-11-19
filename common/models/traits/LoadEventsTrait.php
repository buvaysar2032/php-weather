<?php

namespace common\models\traits;

use common\components\events\ModelLoadEvent;
use common\models\AppModel;
use yii\base\InvalidConfigException;

/**
 * Trait LoadEvents
 *
 * @package common\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
trait LoadEventsTrait
{
    /**
     * {@inheritdoc}
     *
     * @throws InvalidConfigException
     */
    public function load($data, $formName = null): bool
    {
        // Если не удалось узнать, где находятся данные
        $event = $this->createLoadEvent($data, $formName);
        if (!$this->beforeLoad($event)) {
            return false;
        }
        $result = parent::load($data, $formName);

        $this->afterLoad($event);
        return $result;
    }

    /**
     * Создание события с загруженными данными
     *
     * @param array       $data     Данные, загружаемые в модель
     * @param string|null $formName Имя загруженной формы
     *
     * @throws InvalidConfigException
     */
    private function createLoadEvent(array &$data, string $formName = null): ModelLoadEvent
    {
        $scope = $formName ?? $this->formName();
        if ($scope === '' && !empty($data)) {
            $filters = &$data;
        } elseif (isset($data[$scope])) {
            $filters = &$data[$scope];
        } else {
            $filters = [];
        }
        $event = new ModelLoadEvent();
        $event->filters = &$filters;
        return $event;
    }

    /**
     * Функция, которая вызывается до загрузки данных в модель
     *
     * @return bool Необходимо ли продолжать загрузку данных
     */
    public function beforeLoad(ModelLoadEvent $event): bool
    {
        $this->trigger(AppModel::EVENT_BEFORE_LOAD, $event);
        return $event->isValid ?? true;
    }

    /**
     * Функция, которая вызывается после загрузки данных в модель
     */
    public function afterLoad(ModelLoadEvent $event): void
    {
        $this->trigger(AppModel::EVENT_AFTER_LOAD, $event);
    }
}
