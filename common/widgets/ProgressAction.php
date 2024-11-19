<?php

namespace common\widgets;

use Yii;
use yii\base\Action;
use yii\web\Response;

/**
 * Class ProgressAction
 *
 * @package widgets
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class ProgressAction extends Action
{
    final public function run(string $name): bool|array|string
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ProgressBar::findCounter($name);
    }
}
