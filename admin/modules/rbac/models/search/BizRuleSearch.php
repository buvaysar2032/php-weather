<?php

namespace admin\modules\rbac\models\search;

use common\components\arrayQuery\ArrayQuery;
use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;

/**
 * Class BizRuleSearch
 *
 * @package admin\modules\rbac\models\search
 */
class BizRuleSearch extends Model
{
    /**
     * Name of the rule
     */
    public ?string $name = null;

    /**
     * The default page size
     */
    public int $pageSize = 25;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            ['name', 'trim'],
            ['name', 'safe'],
        ];
    }

    /**
     * Creates data provider instance with a search query applied
     */
    public function search(array $params): ArrayDataProvider
    {
        $query = new ArrayQuery(Yii::$app->authManager->getRules());

        if ($this->load($params) && $this->validate()) {
            $query->addCondition('name', $this->name ? "~$this->name" : null);
        }

        return new ArrayDataProvider([
            'allModels' => $query->find(),
            'sort' => [
                'attributes' => ['name'],
            ],
            'pagination' => [
                'pageSize' => $this->pageSize
            ]
        ]);
    }
}
