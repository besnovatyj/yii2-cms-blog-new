<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\BlogNew\exceptions;

/**
 * Ошибка при обновлении поста.
 */
class PostUpdateException extends BlogModuleException
{
    private array $validationErrors {
        get {
            return $this->validationErrors;
        }
    }

    public function __construct(array $validationErrors = [], string $message = 'Не удалось обновить пост')
    {
        $this->validationErrors = $validationErrors;
        parent::__construct($message);
    }

}
