<?php

declare(strict_types=1);

namespace Besnovatyj\BlogNew\exceptions;

/**
 * Ошибка при создании поста.
 *
 * Содержит ошибки валидации, чтобы контроллер мог вернуть их пользователю.
 */
class PostCreateException extends BlogModuleException
{
    /** @var array Ошибки валидации модели */
    private array $validationErrors {
        get {
            return $this->validationErrors;
        }
    }

    public function __construct(array $validationErrors = [], string $message = 'Не удалось создать пост')
    {
        $this->validationErrors = $validationErrors;
        parent::__construct($message);
    }

}
