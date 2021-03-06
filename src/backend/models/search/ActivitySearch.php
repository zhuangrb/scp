<?php

namespace backend\models\search;

use common\definitions\Status;
use common\models\Activity;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ActivitySearch represents the model behind the search form about `common\models\Activity`.
 */
class ActivitySearch extends Activity
{
    public $range;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'game_id', 'status', 'created_by', 'updated_by'], 'integer'],
            [['name', 'start_at', 'end_at', 'desc', 'created_at', 'updated_at', 'range'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Activity::find();
        $query->where(['status' => Status::ACTIVE]);
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
            ]
        );

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere(
            [
                'id' => $this->id,
                'game_id' => $this->game_id,
                'status' => $this->status,
            ]
        );
        $query->andFilterWhere(['>=', 'start_at', $this->start_at]);
        $query->andFilterWhere(['<=', 'end_at', $this->end_at]);
        $query->andFilterWhere(['like', 'desc', $this->desc]);

        return $dataProvider;
    }
}