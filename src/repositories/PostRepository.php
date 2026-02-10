<?php

declare(strict_types=1);

namespace Besnovatyj\BlogNew\repositories;

use Besnovatyj\BlogNew\contracts\PostRepositoryInterface;
use Besnovatyj\BlogNew\models\Post;
use yii\data\ActiveDataProvider;

/**
 * Репозиторий постов (реализация на ActiveRecord).
 *
 * Что делает репозиторий:
 * - Инкапсулирует ВСЮ работу с хранилищем данных (БД)
 * - Строит запросы (ActiveQuery)
 * - Сохраняет / удаляет записи
 * - Возвращает модели (Post) или DataProvider
 *
 * Чего репозиторий НЕ делает:
 * - Не содержит бизнес-логику (это сервис)
 * - Не валидирует бизнес-правила (это сервис)
 * - Не знает о HTTP-запросах (это контроллер)
 * - Не форматирует данные для вьюхи (это вьюха)
 *
 * Почему ActiveDataProvider, а не массив?
 * Это прагматичный компромисс. В идеальном мире репозиторий возвращал бы
 * чистый массив + метаданные пагинации. Но в Yii2 ActiveDataProvider —
 * стандартный интерфейс для ListView, GridView и REST-контроллеров.
 * Обёртка поверх него создаст лишний слой абстракции без реальной пользы.
 *
 * Information Expert (GRASP): репозиторий — эксперт по работе с данными.
 * Он знает, как построить оптимальный запрос, какие индексы использовать,
 * как жадно подгрузить связи.
 */
class PostRepository implements PostRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findById(int $id): ?Post
    {
        return Post::findOne($id);
    }

    /**
     * {@inheritdoc}
     *
     * Подгружаем категорию жадно (with), потому что на странице поста
     * она почти наверняка понадобится. Это оптимизация на уровне
     * репозитория — сервису не нужно думать о N+1.
     */
    public function findBySlug(string $slug): ?Post
    {
        return Post::find()
            ->where(['slug' => $slug])
            ->with(['category'])
            ->one();
    }

    /**
     * {@inheritdoc}
     */
    public function findAllPublished(int $pageSize = 10): ActiveDataProvider
    {
        $query = Post::find()
            ->where(['status' => Post::STATUS_PUBLISHED])
            ->with(['category'])
            ->orderBy(['published_at' => SORT_DESC]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
            'sort' => [
                'defaultOrder' => ['published_at' => SORT_DESC],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function findPublishedByCategoryId(int $categoryId, int $pageSize = 10): ActiveDataProvider
    {
        $query = Post::find()
            ->where([
                'status'      => Post::STATUS_PUBLISHED,
                'category_id' => $categoryId,
            ])
            ->with(['category'])
            ->orderBy(['published_at' => SORT_DESC]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * Обратите внимание: репозиторий НЕ вызывает validate().
     * Валидация — ответственность сервиса. Репозиторий — тупая труба в БД.
     * Если данные невалидны, save(false) кинет ошибку БД,
     * но до этого не должно дойти — сервис проверит раньше.
     */
    public function save(Post $post): bool
    {
        // save(false) — пропускаем валидацию в AR, она уже выполнена в сервисе.
        // Это избегает двойной валидации и делает ответственность явной.
        if (!$post->save(false)) {
            throw new \RuntimeException(
                'Не удалось сохранить пост: ' . implode(', ', $post->getFirstErrors())
            );
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Post $post): bool
    {
        if ($post->delete() === false) {
            throw new \RuntimeException("Не удалось удалить пост с ID {$post->id}");
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function existsBySlug(string $slug, ?int $excludeId = null): bool
    {
        $query = Post::find()->where(['slug' => $slug]);

        if ($excludeId !== null) {
            $query->andWhere(['!=', 'id', $excludeId]);
        }

        return $query->exists();
    }
}
