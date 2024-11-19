<?php

namespace admin\modules\rbac\models\search;

use admin\modules\rbac\Module;
use common\components\arrayQuery\ArrayQuery;
use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\rbac\Item;

/**
 * Class AuthItemSearch
 *
 * @package admin\modules\rbac\models\search
 */
class AuthItemSearch extends Model
{
    /**
     * Auth item name
     */
    public ?string $name = null;

    /**
     * Auth item type
     */
    public ?int $type = null;

    /**
     * Auth item description
     */
    public ?string $description = null;

    /**
     * Auth item rule name
     */
    public ?string $ruleName = null;

    /**
     * The default page size
     */
    public int $pageSize = 25;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['name', 'ruleName', 'description'], 'trim'],
            [['type'], 'integer'],
            [['name', 'ruleName', 'description'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'name' => Yii::t(Module::MODULE_MESSAGES, 'Name'),
            'type' => Yii::t(Module::MODULE_MESSAGES, 'Type'),
            'description' => Yii::t(Module::MODULE_MESSAGES, 'Description'),
            'rule' => Yii::t(Module::MODULE_MESSAGES, 'Rule'),
            'data' => Yii::t(Module::MODULE_MESSAGES, 'Data'),
        ];
    }

    /**
     * Creates data provider instance with search query applied
     */
    public function search(array $params): ArrayDataProvider
    {
        $authManager = Yii::$app->authManager;

        if ($this->type === Item::TYPE_ROLE) {
            $items = $authManager->getRoles();
        } else {
            $items = array_filter(
                $authManager->getPermissions(),
                static fn ($item) => !str_starts_with($item->name, '/')
            );
        }

        $query = new ArrayQuery($items);

        $this->load($params);

        if ($this->validate()) {
            $query->addCondition('name', $this->name ? "~$this->name" : null)
                ->addCondition('ruleName', $this->ruleName ? "~$this->ruleName" : null)
                ->addCondition('description', $this->description ? "~$this->description" : null);
        }

        return new ArrayDataProvider([
            'allModels' => $query->find(),
            'sort' => [
                'attributes' => ['name']
            ],
            'pagination' => [
                'pageSize' => $this->pageSize
            ]
        ]);
    }
}
