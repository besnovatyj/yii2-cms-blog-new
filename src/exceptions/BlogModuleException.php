<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\BlogNew\exceptions;

use RuntimeException;

/**
 * Базовое исключение модуля блога.
 *
 * Все исключения модуля наследуются от него.
 * Это позволяет ловить ВСЕ исключения модуля одним catch:
 *
 * ```
 * try {
 *     $service->create($dto);
 * } catch (BlogModuleException $e) {
 *     // Любая ошибка модуля блога
 * }
 * ```
 *
 * Зачем свои исключения, а не стандартные \RuntimeException?
 * 1. Семантика: PostNotFoundException говорит о проблеме больше, чем RuntimeException
 * 2. Гранулярность: контроллер может по-разному обрабатывать "не найден" и "ошибка сохранения"
 * 3. Инкапсуляция: внутренние ошибки модуля не "протекают" наружу
 */
class BlogModuleException extends RuntimeException
{
}
