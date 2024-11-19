<?php

namespace common\components\behaviors;

use common\components\{events\ModelLoadEvent, helpers\ModuleHelper, helpers\UserUrl};
use common\models\{AppActiveRecord, AppModel};
use JetBrains\PhpStorm\ArrayShape;
use Yii;
use yii\base\{Behavior, Event};

/**
 * Поведение для записи в сессию последнего загруженного пользователем фильтра
 *
 * Работает только в моделях, название класса которых содержит слово `Search`
 *
 * @package common\components
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 * @property AppActiveRecord|AppModel $owner
 */
class UserFiltersBehavior extends Behavior
{
    /**
     * Имя сессии для кеширования фильтров.
     */
    private ?string $filterSessionName;

    /**
     * Короткое имя класса.
     */
    private ?string $ownerClass;

    /**
     * {@inheritdoc}
     */
    final public function attach($owner): void
    {
        parent::attach($owner);
        if (!empty($this->owner) && !ModuleHelper::isApiModule() && !headers_sent()) {
            $class = $this->owner::class;
            if (str_ends_with($class, 'Search')) {
                $this->ownerClass = basename(str_replace('\\', DIRECTORY_SEPARATOR, $class));
                $this->filterSessionName = '_userFilter_' . $this->ownerClass;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    final public function events(): array
    {
        return [
            AppModel::EVENT_BEFORE_LOAD => 'eventBeforeLoad',
            AppModel::EVENT_AFTER_LOAD => 'eventAfterLoad'
        ];
    }

    /**
     * Очистка от загруженных фильтров
     *
     * @param Event $event Событие с загруженными данными
     */
    final public function eventBeforeLoad(Event $event): void
    {
        if (!isset($this->filterSessionName)) {
            return;
        }
        if ($event instanceof ModelLoadEvent) {
            $event->isValid = true;
        }
        Yii::$app->session->remove($this->filterSessionName);
    }

    /**
     * Сохранение загруженных фильтров
     *
     * @param ModelLoadEvent $event Событие с загруженными данными
     */
    final public function eventAfterLoad(ModelLoadEvent $event): void
    {
        if (!isset($this->filterSessionName)) {
            return;
        }
        $filters = $event->filters;
        $data = [];
        foreach ($filters as $attribute => $value) {
            $data[$this->ownerClass][$attribute] = $value;
        }
        foreach (UserUrl::$rememberQueryParams as $rememberQueryParam) {
            if ($param = Yii::$app->request->get($rememberQueryParam)) {
                $data[$rememberQueryParam] = $param;
            }
        }
        if (!empty($data)) {
            Yii::$app->session->set($this->filterSessionName, $data);
        }
    }
}
