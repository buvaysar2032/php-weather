<?php

namespace common\modules\mail\controllers;

use admin\controllers\AdminController;
use common\modules\mail\models\Template;
use common\modules\mail\models\TestMailing;
use Exception;
use Pug\Yii\ViewRenderer;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\console\Application;
use yii\data\ArrayDataProvider;
use yii\filters\VerbFilter;
use yii\helpers\{ArrayHelper, Json};
use yii\web\Response;

/**
 * DefaultController implements the CRUD actions for MailTemplate model.
 *
 * @package mail\controllers
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class TemplateController extends AdminController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['delete' => ['POST'], 'render-pug' => ['POST']]
            ]
        ]);
    }

    /**
     * Lists all MailTemplate models.
     */
    public function actionIndex(): string
    {
        $allModels = [];
        foreach (Template::findAll() as $template) {
            $allModels[] = Template::findFiles($template);
        }
        $dataProvider = new ArrayDataProvider(['allModels' => $allModels]);
        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    /**
     * Displays a single Template model.
     */
    public function actionView(string $name): string
    {
        $template = Template::findFiles($name);
        return $this->render('view', ['template' => $template]);
    }

    /**
     * Creates a new MailTemplate model.
     *
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @throws InvalidConfigException
     */
    public function actionCreate(): Response|string
    {
        $name = 'new-name';
        $i = 0;
        while (in_array($name, Template::findAll(), true)) {
            $i++;
            $name = "new-name-$i";
        }
        $template = Template::findFiles($name);

        if ($template->load(Yii::$app->request->post())) {
            $template->saveFiles();
            Yii::$app->session->setFlash('success', "Шаблон $template->name создан успешно");
            return $this->redirect(['view', 'name' => $template->name]);
        }

        return $this->render('create', ['template' => $template]);
    }

    /**
     * Updates an existing MailTemplate model.
     *
     * If the update is successful, the browser will be redirected to the 'view' page.
     *
     * @throws InvalidConfigException
     */
    public function actionUpdate(string $name): Response|string
    {
        $template = Template::findFiles($name);
        if ($template->load(Yii::$app->request->post())) {
            $template->renameFiles($name, $template->name);
            Yii::$app->session->setFlash('success', "Изменения в шаблоне $name сохранены успешно");
            return $this->redirect(['view', 'name' => $template->name]);
        }

        return $this->render('update', ['template' => $template]);
    }

    /**
     * Deletes an existing MailTemplate model.
     *
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @throws Throwable
     */
    public function actionDelete(string $name): Response
    {
        Template::deleteFiles($name);
        Yii::$app->session->setFlash('success', "Шаблон $name удален успешно");
        return $this->redirect(['index']);
    }

    /**
     * Раздел тестирования отправок
     *
     * @throws InvalidConfigException
     */
    public function actionTest(): string
    {
        $model = new TestMailing();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            try {
                $model->send();
                Yii::$app->session->setFlash('success', 'Рассылка успешна');
            } catch (Exception $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }
        return $this->render('_testing_form', ['model' => $model]);
    }

    /**
     * @throws Exception
     */
    public function actionRenderPug(): string
    {
        if (Yii::$app->request->contentType === 'application/json') {
            $request = Json::decode(Yii::$app->request->rawBody);
            $layout = $request['layout'] ?? '';
            $layoutStyle = $request['layoutStyle'] ?? '';
            $content = $request['content'] ?? '';
            $style = $request['style'] ?? '';
        } else {
            $layout = trim(Yii::$app->request->post('layout'), '"');
            $layoutStyle = trim(Yii::$app->request->post('layoutStyle'), '"');
            $content = trim(Yii::$app->request->post('content'), '"');
            $style = trim(Yii::$app->request->post('style'), '"');
        }
        $domain = Yii::$app->request->hostInfo;
        $renderer = new ViewRenderer();
        $commonConfig = ArrayHelper::merge(
            require Yii::getAlias('@common/config/main.php'),
            require Yii::getAlias('@common/config/main-local.php')
        );
        $consoleConfig = ArrayHelper::merge(
            require Yii::getAlias('@console/config/main.php'),
            require Yii::getAlias('@console/config/main-local.php')
        );
        $config = ArrayHelper::merge($commonConfig, $consoleConfig);
        $origApp = Yii::$app;
        $app = new Application($config);
        $app->view->registerCss('body { margin: 0 }');
        $app->view->registerCss($layoutStyle);
        $app->view->registerCss($style);
        $username = '';
        if ($user = Template::getDummyUser()) {
            $username = $user->userExt->first_name . ' ' . $user->userExt->last_name;
            if ($username === ' ') {
                $username = $user->username;
            }
        }
        $variables = [
            'app' => $app,
            'view' => $app->view,
            'domain' => $domain,
            'content' => $renderer->pug->renderString($content, ['domain' => $domain, 'username' => $username]),
        ];
        $result = $renderer->pug->renderString($layout, $variables);
        Yii::$app = $origApp;
        return $result;
    }
}
