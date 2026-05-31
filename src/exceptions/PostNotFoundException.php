<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\BlogNew\exceptions;

/**
 * Выбрасывается, когда запрошенный пост не найден.
 *
 * Контроллер ловит это исключение и возвращает 404.
 * Сервис не знает про HTTP-коды — он знает только,
 * что пост не найден. Маппинг "доменное исключение → HTTP-код"
 * происходит в контроллере.
 */
class PostNotFoundException extends BlogModuleException
{
    /**
     * @param int|string $identifier ID или слаг поста
     */
    public function __construct(int|string $identifier)
    {
        $message = is_int($identifier)
            ? "Пост с ID $identifier не найден"
            : "Пост со слагом «{$identifier}» не найден";

        parent::__construct($message);
    }
}
