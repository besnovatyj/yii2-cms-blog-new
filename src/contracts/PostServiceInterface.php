<?php

declare(strict_types=1);

namespace Besnovatyj\BlogNew\contracts;

use Besnovatyj\BlogNew\dto\PostCreateDto;
use Besnovatyj\BlogNew\dto\PostUpdateDto;
use Besnovatyj\BlogNew\models\Post;

/**
 * Контракт сервиса постов.
 *
 * Сервис — это место, где живёт бизнес-логика.
 * Контроллер не знает, как создаётся пост; он знает только,
 * что нужно вызвать create() и передать DTO.
 *
 * Почему DTO, а не массив или модель?
 * - Массив: нет типизации, нет автокомплита, легко ошибиться в ключе
 * - Модель: контроллер не должен создавать доменные модели
 * - DTO: строго типизированный контейнер данных, иммутабельный,
 *   валидируемый на этапе создания
 *
 * Сервис принимает DTO → валидирует бизнес-правила → работает с репозиторием.
 */
interface PostServiceInterface
{
    /**
     * Получить пост по ID.
     *
     * @param int $id
     * @return Post
     * @throws \Besnovatyj\BlogNew\exceptions\PostNotFoundException
     */
    public function getById(int $id): Post;

    /**
     * Получить пост по слагу (для фронтенда).
     *
     * Отличие от getById(): кидает исключение если пост не опубликован.
     * Это бизнес-правило: посетитель не должен видеть черновики.
     *
     * @param string $slug
     * @return Post
     * @throws \Besnovatyj\BlogNew\exceptions\PostNotFoundException
     */
    public function getPublishedBySlug(string $slug): Post;

    /**
     * Получить список опубликованных постов с пагинацией.
     *
     * @param int $pageSize
     * @return \yii\data\ActiveDataProvider
     */
    public function getPublishedList(int $pageSize = 10): \yii\data\ActiveDataProvider;

    /**
     * Получить посты по категории.
     *
     * @param string $categorySlug Слаг категории
     * @param int $pageSize
     * @return \yii\data\ActiveDataProvider
     * @throws \Besnovatyj\BlogNew\exceptions\CategoryNotFoundException
     */
    public function getPublishedByCategory(string $categorySlug, int $pageSize = 10): \yii\data\ActiveDataProvider;

    /**
     * Получить список всех постов для админки.
     *
     * @param array $filters
     * @param int $pageSize
     * @return \yii\data\ActiveDataProvider
     */
    public function getAdminList(array $filters = [], int $pageSize = 20): \yii\data\ActiveDataProvider;

    /**
     * Создать новый пост.
     *
     * @param PostCreateDto $dto Данные для создания
     * @return Post Созданный пост
     * @throws \Besnovatyj\BlogNew\exceptions\PostCreateException
     */
    public function create(PostCreateDto $dto): Post;

    /**
     * Обновить существующий пост.
     *
     * @param int $id ID поста
     * @param PostUpdateDto $dto Данные для обновления
     * @return Post Обновлённый пост
     * @throws \Besnovatyj\BlogNew\exceptions\PostNotFoundException
     * @throws \Besnovatyj\BlogNew\exceptions\PostUpdateException
     */
    public function update(int $id, PostUpdateDto $dto): Post;

    /**
     * Удалить пост.
     *
     * @param int $id ID поста
     * @throws \Besnovatyj\BlogNew\exceptions\PostNotFoundException
     */
    public function delete(int $id): void;

    /**
     * Опубликовать пост (перевести из черновика в опубликованные).
     *
     * Вынесено в отдельный метод, потому что публикация — это бизнес-действие
     * со своей логикой (установка даты публикации, отправка уведомлений и т.д.),
     * а не просто смена статуса.
     *
     * @param int $id
     * @return Post
     * @throws \Besnovatyj\BlogNew\exceptions\PostNotFoundException
     */
    public function publish(int $id): Post;

    /**
     * Снять пост с публикации.
     *
     * @param int $id
     * @return Post
     * @throws \Besnovatyj\BlogNew\exceptions\PostNotFoundException
     */
    public function unpublish(int $id): Post;
}
