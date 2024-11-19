<?php

namespace common\modules\user\models;

use common\components\helpers\SearchQueryHelper;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * SocialNetworkSearch represents the model behind the search form of `common\modules\user\models\SocialNetwork`.
 *
 * @package user\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class SocialNetworkSearch extends SocialNetwork
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'user_id'], 'integer'],
            [['social_network_id', 'user_auth_id', 'access_token', 'last_auth_date'], 'safe'],
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
        $query = SocialNetwork::find();

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
            'user_id' => $this->user_id
        ]);

        $query->andFilterWhere(['like', 'social_network_id', $this->social_network_id])
            ->andFilterWhere(['like', 'user_auth_id', $this->user_auth_id])
            ->andFilterWhere(['like', 'access_token', $this->access_token]);

        SearchQueryHelper::filterDataRange(['last_auth_date'], $this, $query);

        return $dataProvider;
    }
}
