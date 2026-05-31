<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\BlogNew\services;

use Yii;
use yii\base\Component;
use yii\data\ActiveDataProvider;
use yii\helpers\Inflector;
use Besnovatyj\BlogNew\contracts\PostRepositoryInterface;
use Besnovatyj\BlogNew\contracts\CategoryRepositoryInterface;
use Besnovatyj\BlogNew\contracts\PostServiceInterface;
use Besnovatyj\BlogNew\dto\PostCreateDto;
use Besnovatyj\BlogNew\dto\PostUpdateDto;
use Besnovatyj\BlogNew\events\PostEvent;
use Besnovatyj\BlogNew\exceptions\CategoryNotFoundException;
use Besnovatyj\BlogNew\exceptions\PostCreateException;
use Besnovatyj\BlogNew\exceptions\PostNotFoundException;
use Besnovatyj\BlogNew\exceptions\PostUpdateException;
use Besnovatyj\BlogNew\models\Post;

/**
 * Сервис постов — слой бизнес-логики.
 *
 * ЭТО — СЕРДЦЕ МОДУЛЯ. Вся бизнес-логика живёт здесь.
 *
 * Что делает сервис:
 * 1. Оркестрирует работу: принимает DTO → валидирует → создаёт/обновляет модель
 *    → передаёт в репозиторий → генерирует события
 * 2. Содержит бизнес-правила: "слаг должен быть уникальным",
 *    "при публикации устанавливается дата", "черновик нельзя опубликовать без заголовка"
 * 3. Является единственной точкой входа для бизнес-операций:
 *    контроллер НИКОГДА не обращается к репозиторию напрямую
 *
 * Почему extends Component:
 * - Component даёт систему событий Yii2 (trigger/on)
 * - Можно подписываться на доменные события модуля
 * - Для чистого POPO пришлось бы писать свою систему событий
 *
 * Controller (Information Expert, GRASP):
 * - Сервис — эксперт по бизнес-правилам блога
 * - Он знает, КАК создать пост правильно
 * - Контроллер знает только, ЧТО нужно создать пост
 *
 * Зависимости через конструктор (Constructor Injection):
 * - Все зависимости явные и видны в сигнатуре конструктора
 * - DI-контейнер Yii2 разрешает их автоматически
 * - Легко подменить для тестов
 */
class PostService extends Component implements PostServiceInterface
{
    /**
     * @param PostRepositoryInterface $postRepository Репозиторий постов
     * @param CategoryRepositoryInterface $categoryRepository Репозиторий категорий
     * @param array $config Стандартный конфиг Yii2 Component
     */
    public function __construct(
        private readonly PostRepositoryInterface $postRepository,
        private readonly CategoryRepositoryInterface $categoryRepository,
        array $config = [],
    ) {
        parent::__construct($config);
    }

    // ──────────────────────────────────────────────────────────────
    //  Чтение (Query)
    // ──────────────────────────────────────────────────────────────

    /**
     * {@inheritdoc}
     */
    public function getById(int $id): Post
    {
        $post = $this->postRepository->findById($id);

        if ($post === null) {
            throw new PostNotFoundException($id);
        }

        return $post;
    }

    /**
     * {@inheritdoc}
     *
     * Бизнес-правило: посетитель может видеть только опубликованные посты.
     * Это правило живёт в сервисе, а не в репозитории, потому что
     * репозиторий — тупой CRUD, а "кто что может видеть" — это бизнес-логика.
     */
    public function getPublishedBySlug(string $slug): Post
    {
        $post = $this->postRepository->findBySlug($slug);

        if ($post === null || !$post->isPublished()) {
            throw new PostNotFoundException($slug);
        }

        return $post;
    }

    /**
     * {@inheritdoc}
     */
    public function getPublishedList(int $pageSize = 10): ActiveDataProvider
    {
        return $this->postRepository->findAllPublished($pageSize);
    }

    /**
     * {@inheritdoc}
     *
     * Сервис проверяет, что категория существует, прежде чем
     * запрашивать посты. Это бизнес-валидация, а не работа с данными.
     */
    public function getPublishedByCategory(string $categorySlug, int $pageSize = 10): ActiveDataProvider
    {
        $category = $this->categoryRepository->findBySlug($categorySlug);

        if ($category === null) {
            throw new CategoryNotFoundException($categorySlug);
        }

        return $this->postRepository->findPublishedByCategoryId($category->id, $pageSize);
    }

    // ──────────────────────────────────────────────────────────────
    //  Запись (Command)
    // ──────────────────────────────────────────────────────────────

    /**
     * {@inheritdoc}
     *
     * Процесс создания поста:
     * 1. Создаём модель Post и заполняем из DTO
     * 2. Генерируем слаг, если не задан
     * 3. Проверяем уникальность слага (бизнес-правило)
     * 4. Устанавливаем автора
     * 5. Сохраняем через репозиторий
     * 6. Генерируем событие
     *
     * Валидация пользовательского ввода выполняется в PostForm до вызова сервиса.
     *
     * Каждый шаг — отдельная ответственность, но оркестрация — задача сервиса.
     * Это паттерн "Application Service" из DDD.
     */
    public function create(PostCreateDto $dto): Post
    {
        $post = new Post();
        $this->fillPostFromCreateDto($post, $dto);

        // Бизнес-правило: если слаг не задан — генерируем из заголовка
        if (empty($post->slug)) {
            $post->slug = $this->generateUniqueSlug($post->title);
        }

        // Бизнес-правило: слаг должен быть уникальным
        if ($this->postRepository->existsBySlug($post->slug)) {
            throw new PostCreateException(
                ['slug' => 'Пост с таким URL-слагом уже существует']
            );
        }

        // Автор: из DTO или текущий пользователь
        $post->author_id = $dto->authorId ?? (int)Yii::$app->user->id;

        // Персистентность — делегируем репозиторию
        $this->postRepository->save($post);

        // Генерируем доменное событие (OCP: расширяемость без модификации)
        $this->trigger(PostEvent::EVENT_AFTER_CREATE, new PostEvent($post));

        return $post;
    }

    /**
     * {@inheritdoc}
     */
    public function update(int $id, PostUpdateDto $dto): Post
    {
        // Получаем существующий пост (или выбрасываем PostNotFoundException)
        $post = $this->getById($id);

        $this->fillPostFromUpdateDto($post, $dto);

        // Генерируем слаг, если не задан
        if (empty($post->slug)) {
            $post->slug = $this->generateUniqueSlug($post->title, $post->id);
        }

        // Проверяем уникальность слага (исключая текущий пост)
        if ($this->postRepository->existsBySlug($post->slug, $post->id)) {
            throw new PostUpdateException(
                ['slug' => 'Пост с таким URL-слагом уже существует']
            );
        }

        $this->postRepository->save($post);
        $this->trigger(PostEvent::EVENT_AFTER_UPDATE, new PostEvent($post));

        return $post;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int $id): void
    {
        $post = $this->getById($id);

        $this->postRepository->delete($post);
        $this->trigger(PostEvent::EVENT_AFTER_DELETE, new PostEvent($post));
    }

    /**
     * {@inheritdoc}
     *
     * Публикация — это НЕ просто "status = 1". Это бизнес-операция:
     * - Устанавливается дата публикации (если ещё не установлена)
     * - Генерируется событие (для рассылки, индексации и т.д.)
     * - В будущем могут добавиться проверки (модерация, расписание)
     */
    public function publish(int $id): Post
    {
        $post = $this->getById($id);

        $post->status = Post::STATUS_PUBLISHED;

        // Бизнес-правило: дата публикации устанавливается один раз
        if ($post->published_at === null) {
            $post->published_at = time();
        }

        $this->postRepository->save($post);
        $this->trigger(PostEvent::EVENT_AFTER_PUBLISH, new PostEvent($post));

        return $post;
    }

    /**
     * {@inheritdoc}
     */
    public function unpublish(int $id): Post
    {
        $post = $this->getById($id);

        $post->status = Post::STATUS_DRAFT;

        $this->postRepository->save($post);
        $this->trigger(PostEvent::EVENT_AFTER_UNPUBLISH, new PostEvent($post));

        return $post;
    }

    // ──────────────────────────────────────────────────────────────
    //  Приватные методы
    // ──────────────────────────────────────────────────────────────

    /**
     * Заполнение модели данными из DTO создания.
     *
     * Вынесено в отдельный метод для:
     * 1. Читаемости метода create()
     * 2. Единой точки маппинга DTO → Model
     * 3. Простоты тестирования
     */
    private function fillPostFromCreateDto(Post $post, PostCreateDto $dto): void
    {
        $post->title            = $dto->title;
        $post->content          = $dto->content;
        $post->slug             = $dto->slug;
        $post->category_id      = $dto->categoryId;
        $post->meta_title       = $dto->metaTitle;
        $post->meta_description = $dto->metaDescription;
        $post->status           = $dto->status;

        // Автоматическая генерация excerpt из контента
        $post->excerpt = $this->generateExcerpt($dto->content);
    }

    /**
     * Заполнение модели данными из DTO обновления.
     */
    private function fillPostFromUpdateDto(Post $post, PostUpdateDto $dto): void
    {
        $post->title            = $dto->title;
        $post->content          = $dto->content;
        $post->slug             = $dto->slug;
        $post->category_id      = $dto->categoryId;
        $post->meta_title       = $dto->metaTitle;
        $post->meta_description = $dto->metaDescription;
        $post->status           = $dto->status;

        $post->excerpt = $this->generateExcerpt($dto->content);
    }

    /**
     * Генерация уникального слага из заголовка.
     *
     * Inflector::slug() из Yii2 транслитерирует кириллицу и создаёт
     * URL-safe строку. Если слаг уже занят — добавляем числовой суффикс.
     *
     * @param string $title Заголовок для генерации слага
     * @param int|null $excludeId ID поста для исключения при проверке (обновление)
     * @return string Уникальный слаг
     */
    private function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $baseSlug = Inflector::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        // Подбираем уникальный слаг, добавляя суффикс при необходимости
        while ($this->postRepository->existsBySlug($slug, $excludeId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Генерация краткого описания из содержимого.
     *
     * Убираем HTML-теги, обрезаем до разумной длины.
     * Простая реализация — в реальном проекте можно усложнить.
     */
    private function generateExcerpt(string $content, int $length = 300): string
    {
        $plainText = strip_tags($content);
        $plainText = trim(preg_replace('/\s+/', ' ', $plainText));

        if (mb_strlen($plainText) <= $length) {
            return $plainText;
        }

        // Обрезаем по последнему пробелу, чтобы не резать слова
        $excerpt = mb_substr($plainText, 0, $length);
        $lastSpace = mb_strrpos($excerpt, ' ');

        if ($lastSpace !== false) {
            $excerpt = mb_substr($excerpt, 0, $lastSpace);
        }

        return $excerpt . '…';
    }
}
