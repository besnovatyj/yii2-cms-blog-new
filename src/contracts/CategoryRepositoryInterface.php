<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\BlogNew\contracts;

use Besnovatyj\BlogNew\models\Category;
use RuntimeException;

/**
 * Контракт репозитория категорий.
 *
 * Категория — относительно простая сущность, поэтому интерфейс небольшой.
 * Это нормально — ISP говорит нам, что интерфейс должен содержать
 * ровно столько методов, сколько нужно клиентам. Не больше.
 */
interface CategoryRepositoryInterface
{
    /**
     * @param int $id
     * @return Category|null
     */
    public function findById(int $id): ?Category;

    /**
     * @param string $slug
     * @return Category|null
     */
    public function findBySlug(string $slug): ?Category;

    /**
     * Получить все активные категории.
     *
     * Используется для выпадающих списков, меню и т.д.
     * Возвращает массив, а не DataProvider, потому что категорий
     * обычно немного и пагинация не нужна.
     *
     * @return Category[]
     */
    public function findAllActive(): array;

    /**
     * @param Category $category
     * @return bool
     * @throws RuntimeException
     */
    public function save(Category $category): bool;

    /**
     * @param Category $category
     * @return bool
     * @throws RuntimeException
     */
    public function delete(Category $category): bool;
}
