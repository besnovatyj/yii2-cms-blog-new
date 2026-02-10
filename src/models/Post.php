<?php

declare(strict_types=1);

namespace Besnovatyj\BlogNew\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Модель поста блога (ActiveRecord).
 *
 * ВАЖНЫЙ АРХИТЕКТУРНЫЙ ПРИНЦИП:
 * Модель = описание структуры данных + связи + базовая валидация.
 * Модель НЕ содержит:
 * - Бизнес-логику (это работа сервиса)
 * - Логику выборки (это работа репозитория)
 * - Логику представления (это работа вьюхи)
 *
 * Yii2 ActiveRecord по своей природе нарушает SRP (он и модель, и репозиторий).
 * Наша задача — минимизировать этот ущерб: используем AR только как маппинг
 * на таблицу БД, а всю работу с данными выносим в репозиторий.
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $content
 * @property string|null $excerpt
 * @property int|null $category_id
 * @property int $author_id
 * @property int $status
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property int|null $published_at
 * @property int $created_at
 * @property int $updated_at
 *
 * @property-read Category|null $category
 */
class Post extends ActiveRecord
{
    /**
     * Статусы поста.
     *
     * Вынесены в константы модели, а не в сервис, потому что это
     * неотъемлемая часть доменной модели — они описывают возможные
     * состояния сущности.
     */
    public const int STATUS_DRAFT = 0;
    public const int STATUS_PUBLISHED = 1;
    public const int STATUS_ARCHIVED = 2;

    /**
     * Карта статусов для отображения в UI.
     *
     * Метод, а не константа, потому что:
     * 1. Можно добавить перевод через Yii::t()
     * 2. Массив-константы не поддерживают вызовы функций
     */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_DRAFT     => 'Черновик',
            self::STATUS_PUBLISHED => 'Опубликован',
            self::STATUS_ARCHIVED  => 'В архиве',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%blog_post}}';
    }

    /**
     * Поведения модели.
     *
     * TimestampBehavior автоматически заполняет created_at и updated_at.
     * Это единственная "магия", которую мы оставляем в модели, потому что
     * это инфраструктурная забота (когда запись была создана/изменена),
     * а не бизнес-логика.
     */
    public function behaviors(): array
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * Связь с категорией.
     *
     * Связи — часть описания доменной модели, им место здесь.
     * Они описывают структуру данных, а не бизнес-логику.
     */
    public function getCategory(): ActiveQuery
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * Проверяет, опубликован ли пост.
     *
     * Это допустимый метод в модели — он не выполняет никаких действий,
     * а лишь предоставляет удобный accessor к состоянию объекта.
     * Это как property getter, а не бизнес-логика.
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * Проверяет, является ли пост черновиком.
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }
}
