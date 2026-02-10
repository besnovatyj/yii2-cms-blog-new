<?php

declare(strict_types=1);

namespace Besnovatyj\BlogNew\contracts;

use Besnovatyj\BlogNew\dto\CategoryDto;
use Besnovatyj\BlogNew\exceptions\CategoryNotFoundException;
use Besnovatyj\BlogNew\models\Category;

/**
 * Контракт сервиса категорий.
 */
interface CategoryServiceInterface
{
    /**
     * @param int $id
     * @return Category
     * @throws CategoryNotFoundException
     */
    public function getById(int $id): Category;

    /**
     * Список активных категорий (для меню, фильтров).
     *
     * @return Category[]
     */
    public function getActiveList(): array;

    /**
     * @param CategoryDto $dto
     * @return Category
     */
    public function create(CategoryDto $dto): Category;

    /**
     * @param int $id
     * @param CategoryDto $dto
     * @return Category
     * @throws CategoryNotFoundException
     */
    public function update(int $id, CategoryDto $dto): Category;

    /**
     * @param int $id
     * @throws CategoryNotFoundException
     */
    public function delete(int $id): void;
}
