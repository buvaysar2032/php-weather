<?php

namespace common\modules\user\models;

use common\components\helpers\SearchQueryHelper;
use common\modules\user\enums\Status;
use yii\base\{InvalidConfigException, Model};
use yii\data\ActiveDataProvider;

/**
 * UserSearch represents the model behind the search form of `common\modules\user\models\User`.
 *
 * @package user\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class UserSearch extends User
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'status'], 'integer'],
            Status::validator('status'),
            [['created_at', 'updated_at', 'last_login_at', 'last_ip'], 'safe'],
            [['username', 'auth_source', 'password_reset_token'], 'safe'],
            //Связанные поля
            [['email.is_confirmed', 'userExt.rules_accepted'], 'integer'],
            [['email.value', 'userExt.first_name', 'userExt.middle_name', 'userExt.last_name', 'userExt.phone'], 'safe']
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
        $query = User::find();

        // add conditions that should always apply here
        $dataProvider = SearchQueryHelper::sortableDataProvider(
            [
                'id',
                'username',
                'auth_source',
                'created_at',
                'updated_at',
                'last_login_at',
                'last_ip',
                'status',
                'email.value',
                'email.is_confirmed',
                'userExt.first_name',
                'userExt.middle_name',
                'userExt.last_name',
                'userExt.phone',
                'userExt.rules_accepted'
            ],
            $this,
            $query
        );

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        SearchQueryHelper::filterSimpleSearch(
            ['id', 'status', 'email.is_confirmed', 'userExt.rules_accepted'],
            $this,
            $query
        );

        SearchQueryHelper::filterLikeString(
            [
                'username',
                'auth_source',
                'password_reset_token',
                'email.value',
                'userExt.first_name',
                'userExt.middle_name',
                'userExt.last_name',
                'userExt.phone'
            ],
            $this,
            $query
        );

        SearchQueryHelper::filterDataRange(
            [
                'updated_at',
                'created_at',
                'last_login_at'
            ],
            $this,
            $query
        );

        return $dataProvider;
    }
}
