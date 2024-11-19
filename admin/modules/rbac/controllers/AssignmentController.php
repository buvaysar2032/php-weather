<?php

namespace admin\modules\rbac\controllers;

use admin\controllers\AdminController;
use admin\modules\rbac\{Module, models\AssignmentModel, models\search\AssignmentSearch};
use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\{ContentNegotiator, VerbFilter};
use yii\helpers\ArrayHelper;
use yii\web\{IdentityInterface, NotFoundHttpException, Response};

/**
 * Class AssignmentController
 *
 * @package admin\modules\rbac\controllers
 */
class AssignmentController extends AdminController
{
    /**
     * The class name of the [[identity]] object
     */
    public IdentityInterface|string|null $userIdentityClass = null;

    /**
     * Search class for assignments search
     */
    public string|array $searchClass = [
        'class' => AssignmentSearch::class
    ];

    /**
     * ID column name
     */
    public string $idField = 'id';

    /**
     * Username column name
     */
    public string $usernameField = 'username';

    /**
     * Assignments GridView columns
     */
    public array $gridViewColumns = [];

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();

        if ($this->userIdentityClass === null) {
            $this->userIdentityClass = Yii::$app->user->identityClass;
        }

        if (empty($this->gridViewColumns)) {
            $this->gridViewColumns = [
                $this->idField,
                $this->usernameField,
            ];
        }
    }

    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['get'],
                    'view' => ['get'],
                    'assign' => ['post'],
                    'remove' => ['post'],
                ],
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'only' => ['assign', 'remove'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON
                ]
            ]
        ]);
    }

    /**
     * List of all assignments
     *
     * @throws InvalidConfigException
     */
    public function actionIndex(): string
    {
        $searchModel = Yii::createObject($this->searchClass);

        if ($searchModel instanceof AssignmentSearch) {
            $dataProvider = $searchModel->search(
                Yii::$app->request->queryParams,
                $this->userIdentityClass,
                $this->idField,
                $this->usernameField
            );
        } else {
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        }

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'gridViewColumns' => $this->gridViewColumns
        ]);
    }

    /**
     * Displays a single Assignment model.
     *
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
     */
    public function actionView(int $id): string
    {
        $model = $this->findModel($id);

        return $this->render('view', ['model' => $model, 'usernameField' => $this->usernameField]);
    }

    /**
     * Assign items
     *
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionAssign(int $id): array
    {
        $items = Yii::$app->getRequest()->post('items', []);
        $assignmentModel = $this->findModel($id);
        $assignmentModel->assign($items);

        return $assignmentModel->getItems();
    }

    /**
     * Remove items
     *
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
     */
    public function actionRemove(int $id): array
    {
        $items = Yii::$app->getRequest()->post('items', []);
        $assignmentModel = $this->findModel($id);
        $assignmentModel->revoke($items);

        return $assignmentModel->getItems();
    }

    /**
     * Finds the Assignment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @return AssignmentModel the loaded model
     *
     * @throws NotFoundHttpException if the model cannot be found
     * @throws InvalidConfigException
     */
    protected function findModel(int $id): AssignmentModel
    {
        $class = $this->userIdentityClass;

        if (($user = $class::findIdentity($id)) !== null) {
            return new AssignmentModel($user);
        }

        throw new NotFoundHttpException(Yii::t(Module::MODULE_MESSAGES, 'The requested page does not exist.'));
    }
}
