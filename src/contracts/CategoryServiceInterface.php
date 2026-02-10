<?php

declare(strict_types=1);

namespace Besnovatyj\BlogNew\contracts;

use Besnovatyj\BlogNew\dto\CategoryDto;
use Besnovatyj\BlogNew\models\Category;

/**
 * Контракт сервиса категорий.
 */
interface CategoryServiceInterface
{
    /**
     * @param int $id
     * @return Category
     * @throws \Besnovatyj\BlogNew\exceptions\CategoryNotFoundException
     */
    public function getById(int $id): Category;

    /**
     * Список активных категорий (для меню, фильтров).
     *
     * @return Category[]
     */
    public function getActiveList(): array;

    /**
     * Список всех категорий для админки.
     *
     * @return \yii\data\ActiveDataProvider
     */
    public function getAdminList(): \yii\data\ActiveDataProvider;

    /**
     * @param CategoryDto $dto
     * @return Category
     */
    public function create(CategoryDto $dto): Category;

    /**
     * @param int $id
     * @param CategoryDto $dto
     * @return Category
     * @throws \Besnovatyj\BlogNew\exceptions\CategoryNotFoundException
     */
    public function update(int $id, CategoryDto $dto): Category;

    /**
     * @param int $id
     * @throws \Besnovatyj\BlogNew\exceptions\CategoryNotFoundException
     */
    public function delete(int $id): void;
}
