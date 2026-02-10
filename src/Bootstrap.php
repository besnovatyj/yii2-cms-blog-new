<?php

declare(strict_types=1);

namespace Besnovatyj\BlogNew;

use yii\base\Application;
use yii\base\BootstrapInterface;

/**
 * Бутстрап модуля блога.
 *
 * Отвечает ТОЛЬКО за регистрацию URL-правил на этапе bootstrap.
 * Это лёгкий класс без зависимостей — создаётся на каждый запрос,
 * но не тянет за собой инстанциирование Module и его DI-привязок.
 *
 * DI-привязки регистрируются в Module::init(), который вызывается
 * только когда URL матчится на маршрут модуля (lazy loading).
 */
class Bootstrap implements BootstrapInterface
{
    /**
     * Регистрация URL-правил модуля.
     *
     * Маршруты определяются внутри модуля, а не в конфиге приложения.
     * Это обеспечивает инкапсуляцию: модуль — самодостаточная единица,
     * которую можно подключить одной строчкой.
     *
     * @param Application $app
     */
    public function bootstrap($app): void
    {

        // $app->on(Application::EVENT_BEFORE_REQUEST, function () {
            // остальной код
        //});

        $app->getUrlManager()->addRules([
            // Фронтенд: красивые URL для посетителей
            'blog'                        => 'blog/post/index',
            'blog/category/<slug:\w+>'    => 'blog/post/category',
            'blog/<slug:[\w-]+>'          => 'blog/post/view',

            // Бэкенд: CRUD-маршруты для постов
            'blog/manage'                 => 'blog/post/index',
            'blog/manage/create'          => 'blog/post/create',
            'blog/manage/<id:\d+>/update' => 'blog/post/update',
            'blog/manage/<id:\d+>/delete' => 'blog/post/delete',

            // Бэкенд: CRUD-маршруты для категорий
            'blog/manage/categories'                 => 'blog/category/index',
            'blog/manage/categories/create'          => 'blog/category/create',
            'blog/manage/categories/<id:\d+>/update' => 'blog/category/update',
            'blog/manage/categories/<id:\d+>/delete' => 'blog/category/delete',
        ], false);
    }
}
