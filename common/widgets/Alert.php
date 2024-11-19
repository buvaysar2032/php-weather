<?php

namespace common\widgets;

use Exception;
use Throwable;
use Yii;
use yii\bootstrap5\{Alert as YiiAlert, Html, Widget};

/**
 * Alert widget renders a message from session flash. All flash messages are displayed
 * in the sequence they were assigned using setFlash. You can set message as following:
 *
 * ```php
 * Yii::$app->session->setFlash('error', 'This is the message');
 * Yii::$app->session->setFlash('success', 'This is the message');
 * Yii::$app->session->setFlash('info', 'This is the message');
 * ```
 *
 * Multiple messages could be set as follows:
 *
 * ```php
 * Yii::$app->session->setFlash('error', ['Error 1', 'Error 2']);
 * ```
 *
 * @author  Kartik Visweswaran <kartikv2@gmail.com>
 * @author  Alexander Makarov <sam@rmcreative.ru>
 * @package widgets
 */
class Alert extends Widget
{
    /**
     * The alert types configuration for the flash messages.
     *
     * This array is setup as $key => $value, where:
     * - key: the name of the session flash variable
     * - value: the bootstrap alert type (i.e. danger, success, info, warning)
     */
    public array $alertTypes = [
        'error' => 'alert-danger',
        'danger' => 'alert-danger',
        'success' => 'alert-success',
        'info' => 'alert-info',
        'warning' => 'alert-warning'
    ];
    /**
     * The options for rendering the close button tag.
     *
     * Array will be passed to [[\yii\bootstrap5\Alert::closeButton]].
     */
    public array $closeButton = [];

    /**
     * {@inheritdoc}
     * @throws Exception|Throwable
     */
    final public function run(): void
    {
        $session = Yii::$app->session;
        $flashes = $session->getAllFlashes();
        $defaultClass = $this->options['class'] ?? [];
        foreach ($flashes as $type => $flash) {
            if (!isset($this->alertTypes[$type])) {
                continue;
            }
            foreach ((array)$flash as $i => $message) {
                $this->options['id'] = "$this->id-$type-$i";
                $this->options['class'] = $defaultClass;
                Html::addCssClass($this->options, $this->alertTypes[$type]);
                echo YiiAlert::widget(
                    ['body' => $message, 'closeButton' => $this->closeButton, 'options' => $this->options]
                );
            }

            $session->removeFlash($type);
        }
    }
}
