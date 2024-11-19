<?php

use yii\helpers\StringHelper;

/**
 * This is the template for generating a CRUD controller class file.
 *
 * @var $this yii\web\View
 * @var $generator yii\gii\generators\crud\Generator
 */

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
    $searchModelAlias = $searchModelClass . 'Search';
}

/** @var yii\db\ActiveRecordInterface $class */
$class = $generator->modelClass;
$pks = $class::primaryKey();
$urlParams = $generator->generateUrlParams();
$actionParams = $generator->generateActionParams();
$actionParamComments = $generator->generateActionParamComments();
foreach ($actionParamComments as &$actionParamComment) {
    $actionParamComment = str_replace('integer', 'int', $actionParamComment);
}
unset($actionParamComment);
$namespace = StringHelper::dirname(ltrim($generator->controllerClass, '\\'));

$includes = [
    ltrim($generator->baseControllerClass, '\\'),
    ltrim($generator->modelClass, '\\'),
    admin\modules\rbac\components\RbacHtml::class,
    kartik\grid\EditableColumnAction::class,
    Yii::class,
    yii\base\InvalidConfigException::class,
    yii\db\StaleObjectException::class,
    yii\web\NotFoundHttpException::class,
    yii\web\Response::class,
    yii\filters\VerbFilter::class,
    yii\helpers\ArrayHelper::class,
    common\components\helpers\UserUrl::class,
    Throwable::class
];
if (!empty($generator->searchModelClass)) {
    $includes[] = ltrim($generator->searchModelClass, '\\') .
        (isset($searchModelAlias) ? " as $searchModelAlias" : "");
} else {
    $includes[] = yii\data\ActiveDataProvider::class;
}
sort($includes, SORT_NATURAL | SORT_FLAG_CASE);

echo "<?php\n";
?>

namespace <?= $namespace ?>;

use <?= implode(";\nuse ", $includes) ?>;

/**
 * <?= $controllerClass ?> implements the CRUD actions for <?= $modelClass ?> model.
 *
 * @package <?= "$namespace\n" ?>
 */
final class <?= $controllerClass ?> extends <?= StringHelper::basename($generator->baseControllerClass) . "\n" ?>
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['delete' => ['POST']]
            ]
        ]);
    }

    /**
     * Lists all <?= $modelClass ?> models.
     *
     * @throws InvalidConfigException
     */
    public function actionIndex(): string
    {
        $model = new <?= $modelClass ?>();

        if (RbacHtml::isAvailable(['create']) && $model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', "Элемент №$model->id создан успешно");
        }

<?php if (!empty($generator->searchModelClass)): ?>
        $searchModel = new <?= $searchModelAlias ?? $searchModelClass ?>();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render(
            'index',
            ['searchModel' => $searchModel, 'dataProvider' => $dataProvider, 'model' => $model]
        );
<?php else: ?>
        $dataProvider = new ActiveDataProvider(['query' => <?= $modelClass ?>::find()]);

        return $this->render('index', ['dataProvider' => $dataProvider, 'model' => $model]);
<?php endif; ?>
    }

    /**
     * Displays a single <?= $modelClass ?> model.
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView(<?= ($actionParams === '$id') ? "int $actionParams" : $actionParams ?>): string
    {
        return $this->render('view', ['model' => $this->findModel(<?= $actionParams ?>)]);
    }

    /**
     * Creates a new <?= $modelClass ?> model.
     *
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @param string|null $redirect если нужен иной редирект после успешного создания
     *
     * @throws InvalidConfigException
     */
    public function actionCreate(string $redirect = null): Response|string
    {
        $model = new <?= $modelClass ?>();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', "Элемент №$model->id создан успешно");
            return match ($redirect) {
                'create' => $this->redirect(['create']),
                'index' => $this->redirect(UserUrl::setFilters(<?= $searchModelAlias ?? $searchModelClass ?>::class)),
                default => $this->redirect(['view', <?= $urlParams ?>])
            };
        }

        return $this->render('create', ['model' => $model]);
    }

    /**
     * Updates an existing <?= $modelClass ?> model.
     *
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @throws NotFoundHttpException if the model cannot be found
     * @throws InvalidConfigException
     */
    public function actionUpdate(<?= ($actionParams === '$id') ? "int $actionParams" : $actionParams ?>): Response|string
    {
        $model = $this->findModel(<?= $actionParams ?>);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', "Элемент №$model->id изменен успешно");
            return $this->redirect(['view', <?= $urlParams ?>]);
        }

        return $this->render('update', ['model' => $model]);
    }

    /**
     * Deletes an existing <?= $modelClass ?> model.
     *
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @throws NotFoundHttpException if the model cannot be found
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function actionDelete(<?= ($actionParams === '$id') ? "int $actionParams" : $actionParams ?>): Response
    {
        $this->findModel(<?= $actionParams ?>)->delete();
        Yii::$app->session->setFlash('success', "Элемент №<?= $actionParams ?> удален успешно");
        return $this->redirect(UserUrl::setFilters(<?= $searchModelAlias ?? $searchModelClass ?>::class));
    }

    /**
     * Finds the <?= $modelClass ?> model based on its primary key value.
     *
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    private function findModel(<?= ($actionParams === '$id') ? "int $actionParams" : $actionParams ?>): <?= $modelClass . PHP_EOL ?>
    {
<?php
if (count($pks) === 1) {
    $condition = '$id';
} else {
    $condition = [];
    foreach ($pks as $pk) {
        $condition[] = "'$pk' => \$$pk";
    }
    $condition = '[' . implode(', ', $condition) . ']';
}
?>
        if (($model = <?= $modelClass ?>::findOne(<?= $condition ?>)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(<?= $generator->generateString('The requested page does not exist.') ?>);
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'change' => [
                'class' => EditableColumnAction::class,
                'modelClass' => <?= $modelClass ?>::class
            ]
        ];
    }
}
