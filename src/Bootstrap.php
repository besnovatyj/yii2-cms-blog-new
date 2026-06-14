<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

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

        // TODO - по идее, когда объединю со старым модулем блога, можно и это вернуть
        //  Хотя, следуя новым принципам можно вынести куда-то в отдельный функционал установку маршрутов, тогда и в composer.json не обязательно этот файл будет совать

//        $app->getUrlManager()->addRules([
//            // Фронтенд: красивые URL для посетителей
//            'BlogNew'                        => 'BlogNew/post/index',
//            'BlogNew/category/<slug:\w+>'    => 'BlogNew/post/category',
//            'BlogNew/<slug:[\w-]+>'          => 'BlogNew/post/view',
//
//            // Бэкенд: CRUD-маршруты для постов
//            'BlogNew/manage'                 => 'BlogNew/post/index',
//            'BlogNew/manage/create'          => 'BlogNew/post/create',
//            'BlogNew/manage/<id:\d+>/update' => 'BlogNew/post/update',
//            'BlogNew/manage/<id:\d+>/delete' => 'BlogNew/post/delete',
//
//            // Бэкенд: CRUD-маршруты для категорий
//            'BlogNew/manage/categories'                 => 'BlogNew/category/index',
//            'BlogNew/manage/categories/create'          => 'BlogNew/category/create',
//            'BlogNew/manage/categories/<id:\d+>/update' => 'BlogNew/category/update',
//            'BlogNew/manage/categories/<id:\d+>/delete' => 'BlogNew/category/delete',
//        ], false);
    }
}
