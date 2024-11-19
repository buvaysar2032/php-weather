<?php

namespace api\controllers;

use api\modules\v1\controllers\AppController;
use RequirementChecker;
use Yii;
use yii\helpers\ArrayHelper;

class SiteController extends AppController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), ['auth' => ['except' => ['health']]]);
    }

    public function actionHealth(): array
    {
        require_once dirname(__DIR__, 2) . '/requirements/RequirementChecker.php';
        $requirementsChecker = new RequirementChecker();
        $requirementsChecker->checkYii();
        if (!empty($requirementsChecker->result['summary']['errors'])) {
            Yii::$app->response->statusCode = 500;
            return $this->returnError('Errors', $requirementsChecker->result['summary']['errors']);
        }
        return $this->returnSuccess(['message' => 'OK']);
    }
}
