<?php

namespace admin\modules\modelExportImport\models;

use common\components\helpers\SearchQueryHelper;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ModelImportLogSearch represents the model behind the search form of `admin\modules\modelExportImport\models\ModelImportLog`.
 *
 * @package modelExportImport\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class ModelImportLogSearch extends ModelImportLog
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            ['id', 'integer'],
            [['model_class', 'unique_field', 'unique_field_value', 'dump_before', 'dump_after', 'imported_at'], 'safe'],
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
     * Creates data provider instance with a search query applied
     *
     * @throws InvalidConfigException
     */
    public function search(array $params): ActiveDataProvider
    {
        $query = ModelImportLog::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider(['query' => $query]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere(['id' => $this->id]);

        $query->andFilterWhere(['like', 'model_class', $this->model_class])
            ->andFilterWhere(['like', 'unique_field', $this->unique_field])
            ->andFilterWhere(['like', 'unique_field_value', $this->unique_field_value])
            ->andFilterWhere(['like', 'dump_before', $this->dump_before])
            ->andFilterWhere(['like', 'dump_after', $this->dump_after]);

        SearchQueryHelper::filterDataRange(['imported_at'], $this, $query);

        return $dataProvider;
    }
}
