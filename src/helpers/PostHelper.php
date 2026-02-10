<?php

declare(strict_types=1);

namespace Besnovatyj\BlogNew\helpers;

use Besnovatyj\BlogNew\models\Post;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Хелпер для презентационной логики постов.
 *
 * Содержит методы генерации HTML-элементов для отображения
 * в админке: бейджи статусов, ссылки на переключение и т.п.
 *
 * Это чистая презентационная логика, которой не место ни в модели,
 * ни в сервисе. Хелпер знает о Bootstrap-классах и URL-маршрутах —
 * это зона ответственности слоя представления.
 */
class PostHelper
{
    /**
     * CSS-классы бейджей для статусов.
     */
    private const array STATUS_CLASSES = [
        Post::STATUS_DRAFT     => 'badge rounded-pill text-bg-secondary',
        Post::STATUS_PUBLISHED => 'badge rounded-pill text-bg-success',
        Post::STATUS_ARCHIVED  => 'badge rounded-pill text-bg-warning',
    ];

    /**
     * Генерирует кликабельный бейдж статуса поста.
     *
     * Для черновика и архива — ссылка на публикацию.
     * Для опубликованного — ссылка на снятие с публикации.
     *
     * @param Post $post
     * @return string HTML-ссылка с бейджем
     */
    public static function statusLabel(Post $post): string
    {
        $labels = Post::statusLabels();
        $label = $labels[$post->status] ?? '?';
        $class = self::STATUS_CLASSES[$post->status] ?? 'badge rounded-pill text-bg-light';

        $badge = Html::tag('span', $label, ['class' => $class]);

        if ($post->isPublished()) {
            $url = Url::to(['unpublish', 'id' => $post->id]);
        } else {
            $url = Url::to(['publish', 'id' => $post->id]);
        }

        return Html::a($badge, $url, [
            'data' => [
                'confirm' => 'Сменить статус?',
                'method'  => 'post',
            ],
        ]);
    }

    /**
     * Генерирует текстовый бейдж статуса (без ссылки).
     *
     * Для использования во фронтенде или там, где не нужна интерактивность.
     *
     * @param Post $post
     * @return string HTML-бейдж
     */
    public static function statusBadge(Post $post): string
    {
        $labels = Post::statusLabels();
        $label = $labels[$post->status] ?? '?';
        $class = self::STATUS_CLASSES[$post->status] ?? 'badge rounded-pill text-bg-light';

        return Html::tag('span', $label, ['class' => $class]);
    }
}
