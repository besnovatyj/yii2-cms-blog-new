<?php

declare(strict_types=1);

namespace Besnovatyj\BlogNew\services;

use yii\base\Component;
use yii\helpers\Inflector;
use Besnovatyj\BlogNew\contracts\CategoryRepositoryInterface;
use Besnovatyj\BlogNew\contracts\CategoryServiceInterface;
use Besnovatyj\BlogNew\dto\CategoryDto;
use Besnovatyj\BlogNew\exceptions\CategoryNotFoundException;
use Besnovatyj\BlogNew\models\Category;

/**
 * Сервис категорий.
 *
 * Относительно простой сервис, но отделён от PostService
 * по SRP: каждый сервис отвечает за свою сущность.
 */
class CategoryService extends Component implements CategoryServiceInterface
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
        array $config = [],
    ) {
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function getById(int $id): Category
    {
        $category = $this->categoryRepository->findById($id);

        if ($category === null) {
            throw new CategoryNotFoundException($id);
        }

        return $category;
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveList(): array
    {
        return $this->categoryRepository->findAllActive();
    }

    /**
     * {@inheritdoc}
     */
    public function create(CategoryDto $dto): Category
    {
        $category = new Category();

        $category->title      = $dto->title;
        $category->slug       = $dto->slug ?? Inflector::slug($dto->title);
        $category->sort_order = $dto->sortOrder;
        $category->is_active  = $dto->isActive;

        $this->categoryRepository->save($category);

        return $category;
    }

    /**
     * {@inheritdoc}
     */
    public function update(int $id, CategoryDto $dto): Category
    {
        $category = $this->getById($id);

        $category->title      = $dto->title;
        $category->slug       = $dto->slug ?? Inflector::slug($dto->title);
        $category->sort_order = $dto->sortOrder;
        $category->is_active  = $dto->isActive;

        $this->categoryRepository->save($category);

        return $category;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int $id): void
    {
        $category = $this->getById($id);
        $this->categoryRepository->delete($category);
    }
}
