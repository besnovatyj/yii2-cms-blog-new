<?php

declare(strict_types=1);

namespace Besnovatyj\BlogNew\forms\backend\search;

use Besnovatyj\BlogNew\models\Category;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Форма фильтрации категорий для GridView в админке.
 */
class CategorySearch extends Model
{
    public ?int $id = null;
    public ?string $title = null;
    public ?int $is_active = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'is_active'], 'integer'],
            [['title'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id'        => 'ID',
            'title'     => 'Название',
            'is_active' => 'Активна',
        ];
    }

    /**
     * Построение отфильтрованного DataProvider для GridView.
     *
     * @param array $params Параметры запроса (Yii::$app->request->queryParams)
     * @return ActiveDataProvider
     */
    public function search(array $params): ActiveDataProvider
    {
        $query = Category::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['sort_order' => SORT_ASC, 'id' => SORT_ASC],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id'        => $this->id,
            'is_active' => $this->is_active,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title]);

        return $dataProvider;
    }
}
