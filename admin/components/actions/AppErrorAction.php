<?php

namespace admin\components\actions;

use yii\web\{ErrorAction, HttpException};

/**
 * Class AppErrorAction
 *
 * @package admin\components\actions
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class AppErrorAction extends ErrorAction
{
    /**
     * Path to 404 custom view
     */
    public string $notFoundView;

    final public function run(): string
    {
        if (isset($this->notFoundView) && $this->exception instanceof HttpException && $this->exception->statusCode === 404) {
            return $this->controller->render($this->notFoundView);
        }
        return parent::run();
    }
}
