<?php

namespace common\components\exceptions;

use Throwable;
use Yii;
use yii\base\Model;

/**
 * Исключение, выводящее ошибки сохранения модели в понятном виде
 *
 * Использование:
 * ```php
 * if (!$record->save()) {
 *     throw new ModelSaveException($record);
 * }
 * ```
 *
 * @package common\components\exceptions
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class ModelSaveException extends BaseException
{
    /**
     * {@inheritdoc}
     */
    public function __construct(Model $model, int $code = 500, Throwable $previous = null)
    {
        $explode = explode('\\', $model::class);
        $messages = $model->errors;
        $message_final = array_pop($explode) . Yii::t('app/error', ' save error: ');
        if (is_array($messages)) {
            foreach ($messages as $key => $message) {
                $message_final .= PHP_EOL;
                $message_final .= $key . ' - ';
                if (is_array($message)) {
                    $message_final .= implode(' ', $message);
                } else {
                    $message_final .= $message;
                }
            }
        } else {
            $message_final = $messages;
        }
        parent::__construct($message_final, $code, $previous);
    }
}
