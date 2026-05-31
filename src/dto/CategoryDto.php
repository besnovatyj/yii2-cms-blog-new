<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\BlogNew\dto;

/**
 * DTO категории.
 *
 * Для категории один DTO на create и update — допустимо,
 * потому что набор полей идентичен и вряд ли разойдётся.
 * Не стоит плодить классы ради "чистоты" — прагматизм важнее.
 */
final readonly class CategoryDto
{
    public function __construct(
        public string  $title,
        public ?string $slug = null,
        public int     $sortOrder = 0,
        public bool    $isActive = true,
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
            slug: $data['slug'] ?? null,
            sortOrder: (int)($data['sort_order'] ?? 0),
            isActive: (bool)($data['is_active'] ?? true),
        );
    }
}
