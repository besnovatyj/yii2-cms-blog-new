<?php

declare(strict_types=1);

namespace Besnovatyj\BlogNew\exceptions;

/**
 * Выбрасывается, когда запрошенная категория не найдена.
 */
class CategoryNotFoundException extends BlogModuleException
{
    public function __construct(int|string $identifier)
    {
        $message = is_int($identifier)
            ? "Категория с ID {$identifier} не найдена"
            : "Категория со слагом «{$identifier}» не найдена";

        parent::__construct($message, 0, null);
    }
}
