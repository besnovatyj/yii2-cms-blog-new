<?php

declare(strict_types=1);

namespace Besnovatyj\BlogNew\dto;

/**
 * DTO для создания нового поста.
 *
 * Что такое DTO и зачем он нужен:
 *
 * DTO (Data Transfer Object) — это простой объект для передачи данных между слоями.
 * Он НЕ содержит бизнес-логики, НЕ валидирует данные (это делает сервис),
 * НЕ знает ничего о базе данных.
 *
 * Зачем, если есть массивы?
 * 1. Типобезопасность: IDE подскажет, какие поля есть, их типы
 * 2. Контракт: чётко видно, какие данные нужны для создания поста
 * 3. Иммутабельность: данные нельзя случайно изменить после создания
 * 4. SRP (Single Responsibility): каждый DTO — одна операция
 *
 * Почему DTO, а не сразу модель Post?
 * - Контроллер не должен создавать доменные модели (это работа сервиса)
 * - DTO может содержать данные, которых нет в модели (например, "отправить уведомление")
 * - При изменении структуры БД меняется модель, но DTO (API контракт) может остаться прежним
 *
 * Паттерн: фабричный метод fromArray() для удобного создания из данных запроса.
 */
final readonly class PostCreateDto
{
    /**
     * @param string $title Заголовок поста
     * @param string $content Содержимое поста (HTML или Markdown)
     * @param int|null $categoryId ID категории (null = без категории)
     * @param string|null $slug SEO-слаг (null = сгенерируется автоматически из title)
     * @param string|null $metaTitle Meta-заголовок для SEO
     * @param string|null $metaDescription Meta-описание для SEO
     * @param int $status Статус поста (по умолчанию — черновик)
     * @param int|null $authorId ID автора (null = текущий пользователь)
     */
    public function __construct(
        public string  $title,
        public string  $content,
        public ?int    $categoryId = null,
        public ?string $slug = null,
        public ?string $metaTitle = null,
        public ?string $metaDescription = null,
        public int     $status = 0,
        public ?int    $authorId = null,
    ) {
    }

    /**
     * Фабричный метод для создания DTO из массива (например, из $request->post()).
     *
     * Инкапсулирует маппинг "ключи массива → свойства объекта".
     * Контроллеру не нужно знать о конструкторе DTO и порядке аргументов.
     *
     * @param array $data Данные из запроса
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: (string)($data['title'] ?? ''),
            content: (string)($data['content'] ?? ''),
            categoryId: isset($data['category_id']) ? (int)$data['category_id'] : null,
            slug: $data['slug'] ?? null,
            metaTitle: $data['meta_title'] ?? null,
            metaDescription: $data['meta_description'] ?? null,
            status: (int)($data['status'] ?? 0),
            authorId: isset($data['author_id']) ? (int)$data['author_id'] : null,
        );
    }
}
