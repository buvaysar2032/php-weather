<?php

namespace admin\controllers;

use admin\components\actions\AppErrorAction;
use admin\models\LoginForm;
use common\widgets\ProgressAction;
use JsonException;
use RequirementChecker;
use Yii;
use yii\base\InvalidConfigException;
use yii\captcha\CaptchaAction;
use yii\filters\VerbFilter;
use yii\helpers\{ArrayHelper, Json};
use yii\web\Response;

/**
 * Главный контроллер панели администратора
 *
 * @package controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class SiteController extends AdminController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['logout' => ['post']]
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action): bool
    {
        if (Yii::$app->controller->action->id === 'logout') {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'error' => AppErrorAction::class,
            'captcha' => [
                'class' => CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null
            ],
            'progress' => ProgressAction::class
        ];
    }

    /**
     * Displays homepage.
     */
    public function actionIndex(): string
    {
        $a = 'администратора';
        $d = 'Добро';
        if (date('d_m') === '01_04') {
            $m = static function ($str) {
                $r = '';
                for ($i = mb_strlen($str); $i >= 0; $i--) {
                    $r .= mb_substr($str, $i, 1);
                }
                return $r;
            };
            $uc = static fn ($str) => mb_strtoupper(mb_substr($str, 0, 1)) . mb_strtolower(mb_substr($str, 1));
            $a = $m($a);
            $d = $uc($m($d));
        }
        $title = "Панель $a";
        $lead = "$d пожаловать!";
        $vueInstalled = file_exists(Yii::getAlias('@vue/dist/.vite/manifest.json'));
        return $this->render('index', ['title' => $title, 'lead' => $lead, 'vueInstalled' => $vueInstalled]);
    }

    public function actionHealth(): string
    {
        require_once dirname(__DIR__, 2) . '/requirements/RequirementChecker.php';
        $requirementsChecker = new RequirementChecker();
        $requirementsChecker->checkYii();
        if (!empty($requirementsChecker->result['summary']['errors'])) {
            Yii::$app->response->statusCode = 500;
            return print_r($requirementsChecker->result['summary']['errors'], true);
        }
        return 'OK';
    }

    /**
     * Logs in a user.
     *
     * @throws InvalidConfigException
     */
    public function actionLogin(): Response|string
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirect(Yii::$app->user->returnUrl);
        }
        return $this->render('login', ['model' => $model]);
    }

    /**
     * Logs out the current user.
     */
    public function actionLogout(): Response
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionSwitchTheme(): Response
    {
        Yii::$app->themeManager->switchTheme();
        if (Yii::$app->request->referrer) {
            return $this->redirect(Yii::$app->request->referrer);
        }
        return $this->goHome();
    }

    /**
     * Страница информации о сервере
     */
    public function actionInfo(): string
    {
        return $this->render('info');
    }

    /**
     * Страница файлового менеджера
     */
    public function actionFileManager(string $type = null): string
    {
        if ($type !== null) {
            return Json::encode($this->renderPartial('../../widgets/ckfinder/views/editor', ['resourceType' => $type]));
        }
        require __DIR__  . '/../../htdocs/ckfinder/keygen.php';
        $sessionCache = getCKEditorSessionKey(
            $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME']
        );
        return $this->render('file-manager', ['sessionCache' => $sessionCache]);
    }

    /**
     * @throws JsonException
     */
    public function actionRegenerateKey(): Response
    {
        $licenseName = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];
        require __DIR__  . '/../../htdocs/ckfinder/keygen.php';
        $sessionCache = getCKEditorSessionKey($licenseName);
        $key = generateLicenseKey(2, $licenseName);
        Yii::$app->session->addFlash('success', 'Сгенерирован ключ "' . $key . '" для лицензии' . $licenseName);
        Yii::$app->session->set($sessionCache, $key);
        Yii::$app->cache->set($sessionCache, $key);
        return $this->redirect(['file-manager']);
    }
}
