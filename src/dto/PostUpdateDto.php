<?php

declare(strict_types=1);

namespace Besnovatyj\BlogNew\dto;

/**
 * DTO для обновления поста.
 *
 * Почему отдельный DTO для update, а не переиспользуем PostCreateDto?
 *
 * 1. SRP: у создания и обновления могут быть разные наборы полей.
 *    Например, при обновлении нельзя менять автора.
 * 2. Семантика: глядя на сигнатуру метода сервиса, сразу понятно,
 *    какие данные он ожидает.
 * 3. Эволюция: если завтра при обновлении появится поле "причина правки",
 *    нам не нужно ломать DTO создания.
 *
 * Это может казаться "лишним кодом", но на практике — экономит часы дебага
 * и делает код самодокументируемым.
 */
final readonly class PostUpdateDto
{
    public function __construct(
        public string  $title,
        public string  $content,
        public ?int    $categoryId = null,
        public ?string $slug = null,
        public ?string $metaTitle = null,
        public ?string $metaDescription = null,
        public int     $status = 0,
    ) {
    }

    /**
     * @param array $data
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
        );
    }
}
