<?php

namespace common\modules\mail\models;

use common\components\helpers\SearchQueryHelper;
use common\enums\AppType;
use common\modules\mail\enums\LogStatus;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * MailingLogSearch represents the model behind the search form of `app\modules\mail\models\MailingLog`.
 *
 * @package mail\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class MailingLogSearch extends MailingLog
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'user_id', 'status', 'app_type', 'mailing_log_id'], 'integer'],
            LogStatus::validator('status'),
            AppType::validator('app_type'),
            [['date', 'mailing_subject', 'mail_to', 'description'], 'safe'],
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
    public function search(array $params, bool $searchErrors = false): ActiveDataProvider
    {
        $query = MailingLog::find();
        if ($searchErrors) {
            $query->where(['status' => 0]);
        }
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
            'user_id' => $this->user_id,
            'status' => $this->status,
            'app_type' => $this->app_type,
            'mailing_log_id' => $this->mailing_log_id,
        ]);

        $query->andFilterWhere(['like', 'mailing_subject', $this->mailing_subject])
            ->andFilterWhere(['like', 'mail_to', $this->mail_to])
            ->andFilterWhere(['like', 'description', $this->description]);

        SearchQueryHelper::filterDataRange('date', $this, $query);

        return $dataProvider;
    }
}
