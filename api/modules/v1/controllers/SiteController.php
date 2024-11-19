<?php

namespace api\modules\v1\controllers;

use admin\models\UserAdmin;
use api\modules\v1\swagger\{JsonAction, ViewAction};
use Yii;
use yii\filters\auth\HttpBasicAuth;
use yii\helpers\{ArrayHelper, Url};

/**
 * Class SiteController
 *
 * @package controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class SiteController extends AppController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'auth' => ['except' => ['docs', 'json']],
            'basicAuth' => [
                'class' => HttpBasicAuth::class,
                'only' => ['docs', 'json'],
                'auth' => static function (string $username, string $password) {
                    $user = UserAdmin::findOne(['username' => $username]);
                    if ($user?->validatePassword($password)) {
                        return $user;
                    }
                    return null;
                }
            ]
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function actions(): array
    {
        if (YII_ENV_DEV) {
            return [
                'docs' => [
                    'class' => ViewAction::class,
                    'apiJsonUrl' => Url::to(['/v1/site/json'], true)
                ],
                'json' => [
                    'class' => JsonAction::class,
                    'dirs' => [
                        Yii::getAlias('@api/modules/v1/controllers/'),
                        Yii::getAlias('@common/models/'),
                        Yii::getAlias('@common/modules/user/actions/'),
                        Yii::getAlias('@common/modules/user/models/User.php')
                    ]
                ]
            ];
        }
        return [];
    }
}
