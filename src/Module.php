<?php

declare(strict_types=1);

namespace Besnovatyj\BlogNew;

use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\Module as BaseModule;
use Besnovatyj\BlogNew\contracts\PostRepositoryInterface;
use Besnovatyj\BlogNew\contracts\CategoryRepositoryInterface;
use Besnovatyj\BlogNew\contracts\PostServiceInterface;
use Besnovatyj\BlogNew\contracts\CategoryServiceInterface;
use Besnovatyj\BlogNew\repositories\PostRepository;
use Besnovatyj\BlogNew\repositories\CategoryRepository;
use Besnovatyj\BlogNew\services\PostService;
use Besnovatyj\BlogNew\services\CategoryService;

/**
 * Модуль блога.
 *
 * Реализует паттерн "Controller → Service → Repository" с инверсией зависимостей (DIP).
 * Модуль самодостаточен: сам регистрирует свои зависимости в DI-контейнере,
 * сам настраивает маршруты — приложению достаточно подключить его в конфиге.
 *
 * Подключение в конфиге приложения:
 *
 * ```php
 * 'modules' => [
 *     'blog' => [
 *         'class' => \Besnovatyj\BlogNew\Module::class,
 *     ],
 * ],
 * 'bootstrap' => ['blog'],
 * ```
 *
 * Почему Module реализует BootstrapInterface:
 * - DI-привязки нужно зарегистрировать ДО того, как контроллер попытается
 *   получить сервис через конструктор. Bootstrap-фаза — единственный момент,
 *   когда мы можем гарантировать это.
 *
 * @property-read string $controllerNamespace
 */
class Module extends BaseModule implements BootstrapInterface
{
    /**
     * Пространство имён контроллеров по умолчанию.
     * Переопределяется в init() в зависимости от того,
     * фронтенд это или бэкенд приложение.
     */
    public $controllerNamespace = 'Besnovatyj\BlogNew\controllers\frontend';

    /**
     * Инициализация модуля.
     *
     * Определяем, в каком приложении работаем (frontend/backend),
     * и настраиваем namespace контроллеров соответственно.
     *
     * Принцип: модуль сам знает, как ему работать в разных контекстах —
     * приложению не нужно об этом думать (Low Coupling, GRASP).
     */
    public function init(): void
    {
        parent::init();

        // Определяем контекст приложения по его id.
        // В advanced-шаблоне Yii2 id обычно 'app-backend' / 'app-frontend'.
        if ($this->isBackendApp()) {
            $this->controllerNamespace = 'Besnovatyj\BlogNew\controllers\backend';
        }
    }

    /**
     * Регистрация зависимостей и маршрутов на этапе bootstrap.
     *
     * Зачем это здесь, а не в init():
     * - bootstrap() вызывается раньше, чем любой контроллер
     * - У нас есть доступ к $app и его компонентам
     * - Можно настроить URL-правила глобально
     *
     * @param Application $app
     */
    public function bootstrap($app): void
    {
        $this->registerDependencies();
        $this->registerRoutes($app);
    }

    /**
     * Регистрация привязок интерфейсов к реализациям в DI-контейнере.
     *
     * Это ключевой момент для соблюдения DIP (Dependency Inversion Principle):
     * - Сервисы зависят от ИНТЕРФЕЙСОВ репозиториев, а не от конкретных классов
     * - Контроллеры зависят от ИНТЕРФЕЙСОВ сервисов
     * - Подмена реализации (например, для тестов) — одна строчка здесь
     *
     * Singleton vs Transient:
     * - Репозитории — singleton, потому что они stateless (не хранят состояние)
     * - Сервисы — singleton по той же причине
     * - Если бы сервис хранил состояние между вызовами, нужен был бы transient
     */
    private function registerDependencies(): void
    {
        $container = Yii::$container;

        // Репозитории: привязка интерфейс → реализация
        $container->setSingleton(PostRepositoryInterface::class, PostRepository::class);
        $container->setSingleton(CategoryRepositoryInterface::class, CategoryRepository::class);

        // Сервисы: привязка интерфейс → реализация
        // DI-контейнер Yii2 сам разрулит зависимости сервисов (их конструкторы
        // принимают интерфейсы репозиториев, которые уже зарегистрированы выше).
        $container->setSingleton(PostServiceInterface::class, PostService::class);
        $container->setSingleton(CategoryServiceInterface::class, CategoryService::class);
    }

    /**
     * Регистрация URL-правил модуля.
     *
     * Маршруты определяются внутри модуля, а не в конфиге приложения.
     * Это обеспечивает инкапсуляцию: модуль — самодостаточная единица,
     * которую можно подключить одной строчкой.
     *
     * @param Application $app
     */
    private function registerRoutes(Application $app): void
    {
        $urlManager = $app->getUrlManager();

        $urlManager->addRules([
            // Фронтенд: красивые URL для посетителей
            'blog'                        => 'blog/post/index',
            'blog/category/<slug:\w+>'    => 'blog/post/category',
            'blog/<slug:[\w-]+>'          => 'blog/post/view',

            // Бэкенд: стандартные CRUD-маршруты для админки
            'blog/manage'                 => 'blog/post/index',
            'blog/manage/create'          => 'blog/post/create',
            'blog/manage/<id:\d+>/update' => 'blog/post/update',
            'blog/manage/<id:\d+>/delete' => 'blog/post/delete',
        ], false); // false = добавить правила, а не перезаписать
    }

    /**
     * Проверяет, работаем ли мы в бэкенд-приложении.
     *
     * Вынесено в отдельный метод для:
     * 1. Читаемости
     * 2. Возможности переопределения в наследниках
     * 3. Удобства тестирования (можно замокать)
     */
    private function isBackendApp(): bool
    {
        return Yii::$app->id === 'app-backend';
    }
}
