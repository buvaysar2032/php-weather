<?php

namespace common\modules\mail\components;

use common\enums\AppType;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * Работа для отложенной отправки email через queue
 *
 * @package mail
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class EmailJob extends BaseObject implements JobInterface
{
    /**
     * Список email адресов для отправки
     */
    public array|string $to;

    /**
     * Массив данных
     */
    public array $data = [];

    /**
     * Инициатор отправки
     */
    public AppType $appType;

    /**
     * Название шаблона
     */
    public string $template;

    /**
     * Тема письма
     */
    public string $subject;

    /**
     * Номер лога отправки, которую пытаемся повторить
     */
    public ?int $previousLogId;

    /**
     * {@inheritdoc}
     */
    final public function execute($queue): void
    {
        $mailer = Yii::$app->mailer;
        $message = $mailer->compose($this->template, $this->data)
            ->setTo($this->to)
            ->setSubject($this->subject);
        $message->appType = $this->appType;
        $message->previousLogId = $this->previousLogId;
        $message->send($mailer);
    }
}
