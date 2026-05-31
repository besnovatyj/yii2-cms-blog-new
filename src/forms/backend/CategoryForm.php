<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\BlogNew\forms\backend;

use Besnovatyj\BlogNew\dto\CategoryDto;
use Besnovatyj\BlogNew\models\Category;
use Besnovatyj\Forms\BaseForm;
use yii\base\Model;

/**
 * Форма создания/редактирования категории в админке.
 *
 * Отвечает за:
 * - Валидацию пользовательского ввода
 * - Подписи полей для ActiveForm
 * - Конвертацию в DTO для передачи в сервисный слой
 */
class CategoryForm extends BaseForm
{
    public ?string $title = null;
    public ?string $slug = null;
    public int $sort_order = 0;
    public bool $is_active = true;

    /**
     * @param Category|null $category Существующая категория (null = создание)
     * @param array $config
     */
    public function __construct(?Category $category = null, array $config = [])
    {
        if ($category !== null) {
            $this->title      = $category->title;
            $this->slug       = $category->slug;
            $this->sort_order = (int)$category->sort_order;
            $this->is_active  = (bool)$category->is_active;
        }

        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['title'], 'required'],
            [['title', 'slug'], 'string', 'max' => 255],
            [['sort_order'], 'integer'],
            [['is_active'], 'boolean'],
            [['sort_order'], 'default', 'value' => 0],
            [['is_active'], 'default', 'value' => true],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'title'      => 'Название',
            'slug'       => 'URL-слаг',
            'sort_order' => 'Порядок сортировки',
            'is_active'  => 'Активна',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function formName(): string
    {
        return 'Category';
    }

    /**
     * Конвертация формы в DTO для сервисного слоя.
     *
     * @return CategoryDto
     */
    public function toDto(): CategoryDto
    {
        return new CategoryDto(
            title: (string)$this->title,
            slug: $this->slug ?: null,
            sortOrder: $this->sort_order,
            isActive: $this->is_active,
        );
    }
}
