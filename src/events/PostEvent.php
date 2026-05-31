<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\BlogNew\events;

use Besnovatyj\BlogNew\models\Post;
use yii\base\Event;

/**
 * Событие, связанное с постом.
 *
 * Зачем доменные события?
 *
 * OCP (Open/Closed Principle): модуль блога открыт для расширения,
 * но закрыт для модификации. Если нужно при публикации поста:
 * - отправить email подписчикам
 * - сбросить кэш
 * - обновить RSS-фид
 * - отправить уведомление в Telegram
 *
 * ...мы НЕ пишем это в PostService. Мы подписываемся на событие:
 *
 * ```php
 * Event::on(
 *     PostService::class,
 *     PostEvent::EVENT_AFTER_PUBLISH,
 *     function (PostEvent $event) {
 *         $mailer->sendNotification($event->post);
 *     }
 * );
 * ```
 *
 * Это декаплинг (Low Coupling, GRASP): модуль блога не знает
 * о модуле рассылок, кэширования, etc.
 */
class PostEvent extends Event
{
    /** Событие после создания поста */
    public const string EVENT_AFTER_CREATE = 'afterPostCreate';

    /** Событие после обновления поста */
    public const string EVENT_AFTER_UPDATE = 'afterPostUpdate';

    /** Событие после удаления поста */
    public const string EVENT_AFTER_DELETE = 'afterPostDelete';

    /** Событие после публикации поста */
    public const string EVENT_AFTER_PUBLISH = 'afterPostPublish';

    /** Событие после снятия с публикации */
    public const string EVENT_AFTER_UNPUBLISH = 'afterPostUnpublish';

    /**
     * @param Post $post Пост, с которым произошло событие
     */
    public function __construct(
        public readonly Post $post,
    ) {
        parent::__construct();
    }
}
