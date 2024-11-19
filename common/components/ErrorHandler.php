<?php

namespace common\components;

use common\components\events\ErrorEvent;
use yii\web\ErrorHandler as BaseErrorHandler;

/**
 * Расширенный ErrorHandler для привязки событий при появлении ошибки
 *
 * @package common\components
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class ErrorHandler extends BaseErrorHandler
{
    public const EVENT_SAVE_ERROR_LOG = 'saveErrorLog';
    public const EVENT_RENDER_EXCEPTION = 'renderException';
    public const EVENT_HANDLE_ERROR = 'handleError';
    public const EVENT_HANDLE_EXCEPTION = 'handleException';
    public const EVENT_HANDLE_FATAL_ERROR = 'handleFatalError';
    public const EVENT_HANDLE_HHVM_ERROR = 'handleHhvmError';

    /**
     * Maximum number of source code lines to be displayed. Defaults to 25.
     */
    public $maxSourceLines = 25;

    /**
     * Maximum number of trace source code lines to be displayed. Defaults to 16.
     */
    public $maxTraceSourceLines = 16;

    /**
     * Maximum number of trace methods' arguments to be displayed. Defaults to 10.
     */
    public int $maxTraceArgumentsInLine = 10;

    /**
     * {@inheritdoc}
     */
    final public function renderException($exception): void
    {
        $event = new ErrorEvent();
        $event->exception = $exception;
        $this->trigger(self::EVENT_RENDER_EXCEPTION, $event);
        parent::renderException($exception);
    }

    /**
     * {@inheritdoc}
     */
    final public function convertExceptionToArray($exception): array
    {
        $array = parent::convertExceptionToArray($exception);
        $array['message'] = explode(PHP_EOL, $array['message']);
        return $array;
    }

    /**
     * {@inheritdoc}
     */
    final public function logException($exception): void
    {
        $event = new ErrorEvent();
        $event->exception = $exception;
        $this->trigger(self::EVENT_SAVE_ERROR_LOG, $event);
        parent::logException($exception);
    }

    /**
     * {@inheritdoc}
     */
    final public function handleError($code, $message, $file, $line): bool
    {
        $this->trigger(self::EVENT_HANDLE_ERROR);
        return parent::handleError($code, $message, $file, $line);
    }

    /**
     * {@inheritdoc}
     */
    final public function handleException($exception): void
    {
        $event = new ErrorEvent();
        $event->exception = $exception;
        $this->trigger(self::EVENT_HANDLE_EXCEPTION, $event);
        parent::handleException($exception);
    }

    /**
     * {@inheritdoc}
     */
    final public function handleFatalError(): void
    {
        $this->trigger(self::EVENT_HANDLE_FATAL_ERROR);
        parent::handleFatalError();
    }

    /**
     * {@inheritdoc}
     */
    final public function handleHhvmError($code, $message, $file, $line, $context, $backtrace): void
    {
        $this->trigger(self::EVENT_HANDLE_HHVM_ERROR);
        parent::handleHhvmError($code, $message, $file, $line, $context, $backtrace);
    }

    /**
     * {@inheritdoc}
     */
    final public function argumentsToString($args): string
    {
        $count = 0;
        $isAssoc = $args !== array_values($args);

        foreach ($args as $key => $value) {
            $count++;
            if ($count >= $this->maxTraceArgumentsInLine) {
                if ($count > $this->maxTraceArgumentsInLine) {
                    unset($args[$key]);
                } else {
                    $args[$key] = '...';
                }
                continue;
            }
            $args[$key] = $this->parseArgumentValue($value);
            if (is_string($key)) {
                $args[$key] = '<span class="string">\'' . $this->htmlEncode($key) . '\'</span> => ' . $args[$key];
            } elseif ($isAssoc) {
                $args[$key] = '<span class="number">' . $key . '</span> => ' . $args[$key];
            }
        }
        return implode(', ', $args);
    }

    private function parseArgumentValue(mixed $value): string
    {
        if (is_object($value)) {
            $value = '<span class="title">' . $this->htmlEncode($value::class) . '</span>';
        } elseif (is_bool($value)) {
            $value = '<span class="keyword">' . ($value ? 'true' : 'false') . '</span>';
        } elseif (is_string($value)) {
            $fullValue = $this->htmlEncode($value);
            if (mb_strlen($value, 'UTF-8') > 32) {
                $displayValue = $this->htmlEncode(mb_substr($value, 0, 32, 'UTF-8')) . '...';
                $value = '<span class="string" title="' . $fullValue . '">\'' . $displayValue . '\'</span>';
            } else {
                $value = '<span class="string">\'' . $fullValue . '\'</span>';
            }
        } elseif (is_array($value)) {
            $value = '[' . $this->argumentsToString($value) . ']';
        } elseif ($value === null) {
            $value = '<span class="keyword">null</span>';
        } elseif (is_resource($value)) {
            $value = '<span class="keyword">resource</span>';
        } else {
            $value = "<span class=\"number\">$value</span>";
        }
        return $value;
    }
}
