<?php

namespace admin\modules\rbac\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

/**
 * Class AssignmentSearch
 *
 * @package admin\modules\rbac\models\search
 */
class AssignmentSearch extends Model
{
    /**
     * User ID
     */
    public ?string $id = null;

    /**
     * Username
     */
    public ?string $username = null;

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
            [['id', 'username'], 'safe'],
        ];
    }

    /**
     * Creates data provider instance with a search query applied
     */
    public function search(
        array $params,
        ActiveRecord|string $class,
        string $idField,
        string $usernameField
    ): ActiveDataProvider {
        $query = $class::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => $this->pageSize]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([$idField => $this->id]);
        $query->andFilterWhere(['like', $usernameField, $this->username]);

        return $dataProvider;
    }
}
