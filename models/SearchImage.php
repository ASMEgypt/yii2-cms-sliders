<?php

namespace infoweb\sliders\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use infoweb\sliders\models\Image;

/**
 * SearchSlider represents the model behind the search form about `app\models\Slider`.
 */
class SearchImage extends Image
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            //[['name'], 'safe'],
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
     *
     * @return ActiveDataProvider
     */
    public function search($params, $id)
    {
        $query = Image::find()->where(['itemId' => $id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['position' => SORT_DESC ]]
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        //$query->andFilterWhere(['like', 'urlAlias', $this->urlAlias]);

        return $dataProvider;
    }
}
