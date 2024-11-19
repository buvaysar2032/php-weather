<?php

namespace common\modules\log\behaviors;

use common\components\helpers\ModuleHelper;
use common\modules\log\{enums\LogOperation, enums\LogStatus, models\Log};
use Yii;
use yii\base\{Behavior, Model};
use yii\db\{ActiveRecord, BaseActiveRecord};
use yii\helpers\Json;

/**
 * Class Logger
 *
 * @package log
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property ActiveRecord $owner Модель к которой прикреплено поведение
 */
class Logger extends Behavior
{
    /**
     * ID записи
     */
    private ?int $record_id = null;

    /**
     * Значения полей до изменения
     */
    private array $before = [];

    /**
     * Значения полей после изменения
     */
    private array $after = [];

    /**
     * {@inheritdoc}
     */
    final public function events(): array
    {
        $events = parent::events();
        if (ModuleHelper::isAdminModule()) {
            $events = array_merge(
                $events,
                [
                    BaseActiveRecord::EVENT_BEFORE_INSERT => 'logBeforeCreate',
                    BaseActiveRecord::EVENT_BEFORE_UPDATE => 'logBeforeUpdate',
                    BaseActiveRecord::EVENT_BEFORE_DELETE => 'logBeforeDelete',
                    BaseActiveRecord::EVENT_AFTER_INSERT => 'logAfterCreate',
                    BaseActiveRecord::EVENT_AFTER_UPDATE => 'logAfterUpdate',
                    BaseActiveRecord::EVENT_AFTER_DELETE => 'logAfterDelete',
                    Model::EVENT_AFTER_VALIDATE => 'logAfterValidate',
                ]
            );
        }
        return $events;
    }

    /**
     * Обработчик EVENT_AFTER_VALIDATE
     *
     * Логирование ошибок валидации
     */
    final public function logAfterValidate(): void
    {
        $this->rememberDiff();
        //Если есть хоть одна ошибка - записываем
        if (count($this->owner->errors) > 0) {
            if ($this->owner->oldAttributes === null) {
                $operation = LogOperation::Insert;
            } elseif ($this->owner->attributes === null) {
                $operation = LogOperation::Delete;
            } else {
                $operation = LogOperation::Update;
            }
            $this->saveLog($operation, LogStatus::Error);
        }
    }

    /**
     * Запомнить разницу значений полей
     */
    private function rememberDiff(): void
    {
        $this->record_id = (int)$this->owner->primaryKey;
        $this->before = array_diff_assoc($this->owner->oldAttributes, $this->owner->attributes);
        $this->after = array_diff_assoc($this->owner->attributes, $this->owner->oldAttributes);
    }

    /**
     * Сохранение лога
     */
    private function saveLog(LogOperation $operation, LogStatus $status): void {
        /** @var \common\modules\log\Log $module */
        $module = Yii::$app->getModule('log');
        if (!$module->enabled || !ModuleHelper::isAdminModule()) {
            return; // Записываем ТОЛЬКО действия в панели администратора, и при включенном модуле
        }
        /** @var array $className */
        $className = explode('\\', $this->owner::class);
        $log = new Log();
        $log->table_model = end($className);
        $log->record_id = $this->record_id;
        if (LogOperation::Delete === $operation) {
            $log->field = Json::encode(array_keys($this->before));
        } else {
            $log->field = Json::encode(array_keys($this->after));
        }
        $log->before = Json::encode($this->before);
        $log->after = Json::encode($this->after);
        $log->operation_type = $operation->value;
        $log->time = time();
        $log->user_admin_id = Yii::$app->user->id;
        $log->user_agent = Yii::$app->request->userAgent;
        $log->ip = Yii::$app->request->userIP;
        $log->status = $status->value;
        $log->description = Json::encode($this->owner->errors);
        if (!$log->validate()) {
            Yii::warning(['Log validate error' => $log->errors], __METHOD__);
            return;
        }
        if (!$log->save()) {
            Yii::warning(['Log save error' => $log->errors], __METHOD__);
            return;
        }
        Yii::info('Log for table ' . $log->table_model . ' saved', __METHOD__);
        unset($log);
    }

    /**
     * Обработчик EVENT_BEFORE_INSERT
     */
    final public function logBeforeCreate(): void
    {
        $this->rememberDiff();
        //Если есть хоть одна ошибка - записываем
        if (count($this->owner->errors) > 0) {
            $this->saveLog(LogOperation::Insert, LogStatus::Error);
        }
    }

    /**
     * Обработчик EVENT_AFTER_INSERT
     */
    final public function logAfterCreate(): void
    {
        $this->after = $this->owner->attributes;
        if (count($this->owner->errors) > 0) {
            $this->saveLog(LogOperation::Insert, LogStatus::Error);
        } else {
            $this->owner->refresh();
            $this->record_id = (int)$this->owner->primaryKey;
            $this->saveLog(LogOperation::Insert, LogStatus::Success);
        }
    }

    /**
     * Обработчик EVENT_BEFORE_UPDATE
     */
    final public function logBeforeUpdate(): void
    {
        $this->rememberDiff();

        //Если есть хоть одна ошибка - записываем
        if (count($this->owner->errors) > 0) {
            $this->saveLog(LogOperation::Update, LogStatus::Error);
        }
    }

    /**
     * Обработчик EVENT_AFTER_UPDATE
     */
    final public function logAfterUpdate(): void
    {
        if (count($this->owner->errors) > 0) {
            $this->saveLog(LogOperation::Update, LogStatus::Error);
        } else {
            $this->saveLog(LogOperation::Update, LogStatus::Success);
        }
    }

    /**
     * Обработчик EVENT_BEFORE_DELETE
     */
    final public function logBeforeDelete(): void
    {
        //Получаем значения полей ДО удаления
        $this->record_id = (int)$this->owner->primaryKey;
        $this->before = $this->owner->oldAttributes;

        //Если есть хоть одна ошибка - записываем
        if (count($this->owner->errors) > 0) {
            $this->saveLog(LogOperation::Delete, LogStatus::Error);
        }
    }

    /**
     * Обработчик EVENT_AFTER_DELETE
     */
    final public function logAfterDelete(): void
    {
        $this->saveLog(LogOperation::Delete, LogStatus::Success);
    }
}