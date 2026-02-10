<?php

declare(strict_types=1);

namespace Besnovatyj\BlogNew\repositories;

use Besnovatyj\BlogNew\contracts\CategoryRepositoryInterface;
use Besnovatyj\BlogNew\models\Category;
use yii\data\ActiveDataProvider;

/**
 * Репозиторий категорий (реализация на ActiveRecord).
 */
class CategoryRepository implements CategoryRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findById(int $id): ?Category
    {
        return Category::findOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findBySlug(string $slug): ?Category
    {
        return Category::find()
            ->where(['slug' => $slug])
            ->one();
    }

    /**
     * {@inheritdoc}
     *
     * Возвращаем массив (не DataProvider), потому что категорий обычно мало
     * и пагинация не нужна. Сортировка по sort_order для корректного
     * отображения в меню.
     */
    public function findAllActive(): array
    {
        return Category::find()
            ->where(['is_active' => true])
            ->orderBy(['sort_order' => SORT_ASC, 'title' => SORT_ASC])
            ->all();
    }

    /**
     * {@inheritdoc}
     */
    public function save(Category $category): bool
    {
        if (!$category->save(false)) {
            throw new \RuntimeException(
                'Не удалось сохранить категорию: ' . implode(', ', $category->getFirstErrors())
            );
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Category $category): bool
    {
        if ($category->delete() === false) {
            throw new \RuntimeException("Не удалось удалить категорию с ID {$category->id}");
        }

        return true;
    }
}
