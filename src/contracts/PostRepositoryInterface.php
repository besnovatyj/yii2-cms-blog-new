<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\BlogNew\contracts;

use Besnovatyj\BlogNew\models\Post;
use RuntimeException;
use yii\data\ActiveDataProvider;

/**
 * Контракт репозитория постов.
 *
 * Зачем интерфейс, если реализация будет одна?
 *
 * 1. DIP (Dependency Inversion Principle): сервис зависит от абстракции,
 *    а не от конкретного ActiveRecord-репозитория. Завтра мы можем перейти
 *    на Elasticsearch для поиска постов — и сервис об этом не узнает.
 *
 * 2. Тестируемость: в юнит-тестах сервиса мы мокаем этот интерфейс,
 *    а не лезем в базу данных.
 *
 * 3. ISP (Interface Segregation): репозиторий предоставляет только те методы,
 *    которые реально нужны сервисному слою. Никаких "God Repository".
 *
 * Соглашения:
 * - Методы find* возвращают null, если запись не найдена (не кидают исключения)
 * - Методы getAll* возвращают массив (пустой, если ничего не найдено)
 * - Методы save/delete кидают исключения при ошибках
 */
interface PostRepositoryInterface
{
    /**
     * Найти пост по первичному ключу.
     *
     * @param int $id ID поста
     * @return Post|null null если пост не найден
     */
    public function findById(int $id): ?Post;

    /**
     * Найти пост по слагу (SEO-friendly URL).
     *
     * Используется на фронтенде для отображения поста используя красивый URL.
     *
     * @param string $slug Уникальный слаг поста
     * @return Post|null
     */
    public function findBySlug(string $slug): ?Post;

    /**
     * Получить опубликованные посты с пагинацией.
     *
     * Возвращает ActiveDataProvider, потому что Yii2 ListView/GridView
     * ожидают именно его. Это компромисс между чистой архитектурой
     * и прагматизмом фреймворка.
     *
     * @param int $pageSize Количество постов на странице
     * @return ActiveDataProvider
     */
    public function findAllPublished(int $pageSize = 10): ActiveDataProvider;

    /**
     * Получить опубликованные посты по категории.
     *
     * @param int $categoryId ID категории
     * @param int $pageSize Количество постов на странице
     * @return ActiveDataProvider
     */
    public function findPublishedByCategoryId(int $categoryId, int $pageSize = 10): ActiveDataProvider;

    /**
     * Сохранить пост (создание или обновление).
     *
     * Репозиторий отвечает только за персистентность.
     * Валидация и бизнес-логика — в сервисе.
     *
     * @param Post $post Модель поста
     * @return bool Успешность сохранения
     * @throws RuntimeException Если сохранение не удалось
     */
    public function save(Post $post): bool;

    /**
     * Удалить пост.
     *
     * @param Post $post Модель поста
     * @return bool Успешность удаления
     * @throws RuntimeException Если удаление не удалось
     */
    public function delete(Post $post): bool;

    /**
     * Проверить, существует ли пост с данным слагом.
     *
     * Нужно для валидации уникальности слага при создании/обновлении поста.
     *
     * @param string $slug Слаг для проверки
     * @param int|null $excludeId ID поста, который нужно исключить (при обновлении)
     * @return bool
     */
    public function existsBySlug(string $slug, ?int $excludeId = null): bool;
}
