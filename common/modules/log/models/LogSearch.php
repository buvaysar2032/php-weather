<?php

namespace common\modules\log\models;

use common\components\helpers\SearchQueryHelper;
use common\modules\log\enums\{LogOperation, LogStatus};
use yii\base\{InvalidConfigException, Model};
use yii\data\ActiveDataProvider;

/**
 * LogSearch represents the model behind the search form of `admin\models\Log`.
 *
 * @package log
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class LogSearch extends Log
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'record_id', 'operation_type', 'user_admin_id', 'status'], 'integer'],
            LogOperation::validator('operation_type'),
            LogStatus::validator('status'),
            ['time', 'safe'],
            [['table_model', 'field', 'before', 'after', 'user_agent', 'ip', 'description'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios(): array
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @throws InvalidConfigException
     */
    public function search(array $params): ActiveDataProvider
    {
        $query = Log::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider(['query' => $query]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'record_id' => $this->record_id,
            'table_model' => $this->table_model,
            'operation_type' => $this->operation_type,
            'user_admin_id' => $this->user_admin_id,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'field', $this->field])
            ->andFilterWhere(['like', 'before', $this->before])
            ->andFilterWhere(['like', 'after', $this->after])
            ->andFilterWhere(['like', 'user_agent', $this->user_agent])
            ->andFilterWhere(['like', 'ip', $this->ip])
            ->andFilterWhere(['like', 'description', $this->description]);

        SearchQueryHelper::filterDataRange('time', $this, $query);

        return $dataProvider;
    }
}
