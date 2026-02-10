<?php

declare(strict_types=1);

namespace Besnovatyj\BlogNew\forms\backend\search;

use Besnovatyj\BlogNew\models\Post;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Форма фильтрации постов для GridView в админке.
 *
 * Это НЕ репозиторий и НЕ сервис. Это специализированная форма поиска —
 * аналог PostForm, но для READ-операции фильтрации списка.
 *
 * Ответственности:
 * - Приём и валидация параметров фильтрации из GET-запроса
 * - Построение запроса с фильтрами и возврат ActiveDataProvider для GridView
 *
 * Чего здесь НЕТ (и не должно быть):
 * - Хелперных методов для UI (statusList, categoryList) — это зона PostHelper
 *   или данные, которые контроллер передаёт во вьюху отдельно
 * - Бизнес-логики — это чистая фильтрация UI
 */
class PostSearch extends Model
{
    public ?int $id = null;
    public ?string $title = null;
    public ?int $status = null;
    public ?int $category_id = null;

    /**
     * Правила валидации для параметров фильтрации.
     *
     * Все поля optional — пустой фильтр возвращает все записи.
     * Правила мягче, чем у PostForm: нет 'required', нет 'max' —
     * это входные данные фильтра, а не данные для записи в БД.
     */
    public function rules(): array
    {
        return [
            [['id', 'status', 'category_id'], 'integer'],
            [['title'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id'          => 'ID',
            'title'       => 'Заголовок',
            'status'      => 'Статус',
            'category_id' => 'Категория',
        ];
    }

    /**
     * Построение отфильтрованного DataProvider для GridView.
     *
     * Паттерн работы:
     * 1. Создаём базовый запрос с eager loading
     * 2. Оборачиваем в ActiveDataProvider (сортировка, пагинация)
     * 3. Загружаем параметры фильтрации из GET
     * 4. Если валидация провалилась — возвращаем пустой результат
     * 5. Применяем фильтры через andFilterWhere (null-safe)
     *
     * @param array $params Параметры запроса (обычно Yii::$app->request->queryParams)
     * @return ActiveDataProvider
     */
    public function search(array $params): ActiveDataProvider
    {
        $query = Post::find()->with('category');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
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
            'id'          => $this->id,
            'status'      => $this->status,
            'category_id' => $this->category_id,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title]);

        return $dataProvider;
    }
}
