<?php

namespace common\modules\mail\components;

use common\enums\AppType;
use Yii;
use yii\mail\MailerInterface;
use yii\queue\Queue;
use yii\symfonymailer\Message as YiiMessage;

class Message extends YiiMessage
{
    /**
     * Тип приложения, который инициировал отправку
     */
    public AppType $appType = AppType::Undefined;
    /**
     * Шаблон письма
     */
    public ?string $template = null;
    /**
     * Данные, которые передали в шаблон письма
     */
    public array $data = [];
    /**
     * Предыдущий лог отправки письма
     */
    public ?int $previousLogId = null;

    /**
     * Асинхронная отправка письма через очередь, если это возможно.
     *
     * Если в компоненте mailer выключен флаг `pushToQueue`, то будет выполнена простая отправка письма.
     * Иначе, отправка будет добавлена в очередь
     *
     * @param Queue|null           $queue  Компонент очереди
     * @param MailerInterface|null $mailer Компонент мейлера
     *
     * @return string|int|bool|null true|false при успешной или неуспешной простой отправке соответственно,
     * или ID новой работы в очереди
     */
    public function sendAsync(Queue $queue = null, MailerInterface $mailer = null): null|string|int|bool
    {
        if (is_null($queue)) {
            $queue = Yii::$app->queue;
        }
        if (is_null($mailer) && is_null($this->mailer)) {
            $mailer = Yii::$app->mailer;
        } elseif (is_null($mailer)) {
            $mailer = $this->mailer;
        }
        if ($this->template && $mailer instanceof Mailer && $mailer->pushToQueue) {
            $nextMailTime = max((int)Yii::$app->cache->get('lastMailTime') + $mailer->delay, time());
            $delay = $nextMailTime - time();
            Yii::$app->cache->set('lastMailTime', $nextMailTime);
            return $queue
                ->delay(max($delay, 0))
                ->push(new EmailJob([
                    'to' => $this->getTo(),
                    'data' => $this->data,
                    'appType' => $this->appType,
                    'template' => $this->template,
                    'subject' => $this->getSubject(),
                    'previousLogId' => $this->previousLogId
                ]));
        }
        return $this->send($mailer);
    }
}
