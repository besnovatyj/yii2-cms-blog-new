<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\BlogNew\forms\backend;

use Besnovatyj\BlogNew\dto\PostCreateDto;
use Besnovatyj\BlogNew\dto\PostUpdateDto;
use Besnovatyj\BlogNew\models\Post;
use Besnovatyj\Forms\BaseForm;
use yii\base\Model;

/**
 * Форма создания/редактирования поста в админке.
 *
 * Отвечает за:
 * - Валидацию пользовательского ввода (rules)
 * - Подписи полей для ActiveForm (attributeLabels)
 * - Конвертацию в DTO для передачи в сервисный слой
 *
 * Модель Post при этом остаётся чистой доменной сущностью:
 * она описывает структуру данных, связи и доменные методы,
 * но не заботится о валидации пользовательского ввода.
 *
 * @property-read bool $isUpdate
 */
class PostForm extends BaseForm
{
    public ?string $title = null;
    public ?string $slug = null;
    public ?string $content = null;
    public ?int $category_id = null;
    public int $status = Post::STATUS_DRAFT;
    public ?string $meta_title = null;
    public ?string $meta_description = null;

    /**
     * @param Post|null $post Существующий пост для заполнения формы (null = создание)
     * @param array $config
     */
    public function __construct(?Post $post = null, array $config = [])
    {
        if ($post !== null) {
            $this->title            = $post->title;
            $this->slug             = $post->slug;
            $this->content          = $post->content;
            $this->category_id      = $post->category_id;
            $this->status           = $post->status;
            $this->meta_title       = $post->meta_title;
            $this->meta_description = $post->meta_description;
        }

        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['title', 'content'], 'required'],

            [['title', 'slug', 'meta_title'], 'string', 'max' => 255],
            [['meta_description'], 'string', 'max' => 500],
            [['content'], 'string'],

            [['category_id', 'status'], 'integer'],

            [['status'], 'default', 'value' => Post::STATUS_DRAFT],
            [['status'], 'in', 'range' => array_keys(Post::statusLabels())],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'title'            => 'Заголовок',
            'slug'             => 'URL-слаг',
            'content'          => 'Содержание',
            'category_id'      => 'Категория',
            'status'           => 'Статус',
            'meta_title'       => 'SEO-заголовок',
            'meta_description' => 'SEO-описание',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function formName(): string
    {
        return 'Post';
    }

    /**
     * Конвертация формы в DTO для создания поста.
     *
     * @return PostCreateDto
     */
    public function toCreateDto(): PostCreateDto
    {
        return new PostCreateDto(
            title: (string)$this->title,
            content: (string)$this->content,
            categoryId: $this->category_id,
            slug: $this->slug ?: null,
            metaTitle: $this->meta_title,
            metaDescription: $this->meta_description,
            status: $this->status,
        );
    }

    /**
     * Конвертация формы в DTO для обновления поста.
     *
     * @return PostUpdateDto
     */
    public function toUpdateDto(): PostUpdateDto
    {
        return new PostUpdateDto(
            title: (string)$this->title,
            content: (string)$this->content,
            categoryId: $this->category_id,
            slug: $this->slug ?: null,
            metaTitle: $this->meta_title,
            metaDescription: $this->meta_description,
            status: $this->status,
        );
    }
}
