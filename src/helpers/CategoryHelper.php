<?php

declare(strict_types=1);

namespace Besnovatyj\BlogNew\helpers;

use Besnovatyj\BlogNew\models\Category;
use yii\helpers\Html;

/**
 * Хелпер для презентационной логики категорий.
 */
class CategoryHelper
{
    /**
     * Список значений активности для фильтров GridView.
     *
     * @return array<int, string>
     */
    public static function activeList(): array
    {
        return [
            0 => 'Неактивна',
            1 => 'Активна',
        ];
    }

    /**
     * Генерирует бейдж статуса активности категории.
     *
     * @param Category $category
     * @return string HTML-бейдж
     */
    public static function activeBadge(Category $category): string
    {
        if ($category->is_active) {
            return Html::tag('span', 'Активна', [
                'class' => 'badge rounded-pill text-bg-success',
            ]);
        }

        return Html::tag('span', 'Неактивна', [
            'class' => 'badge rounded-pill text-bg-secondary',
        ]);
    }
}
