<?php

namespace api\modules\v1\swagger;

use Yii;
use yii\base\Action;
use yii\web\Response;

/**
 * Class ViewAction
 *
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class ViewAction extends Action
{
    /**
     * Open Api Swagger Json URL
     */
    public string $apiJsonUrl;

    /**
     * Action runner
     */
    public function run(): string
    {
        Yii::$app->response->format = Response::FORMAT_HTML;

        return $this->controller->view->renderFile(
            __DIR__ . '/view.php',
            ['apiJsonUrl' => $this->apiJsonUrl],
            $this->controller
        );
    }
}