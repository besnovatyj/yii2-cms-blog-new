<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\BlogNew;

use Besnovatyj\BlogNew\contracts\CategoryRepositoryInterface;
use Besnovatyj\BlogNew\contracts\CategoryServiceInterface;
use Besnovatyj\BlogNew\contracts\PostRepositoryInterface;
use Besnovatyj\BlogNew\contracts\PostServiceInterface;
use Besnovatyj\BlogNew\repositories\CategoryRepository;
use Besnovatyj\BlogNew\repositories\PostRepository;
use Besnovatyj\BlogNew\services\CategoryService;
use Besnovatyj\BlogNew\services\PostService;
use Besnovatyj\Kernel\module\CmsModule;
use Besnovatyj\Contracts\module\DeclaresModule;
use Besnovatyj\Contracts\module\ProvidesBootstrap;
use Besnovatyj\Contracts\module\ProvidesDependencies;
use Besnovatyj\Contracts\module\ProvidesDirectories;
use Besnovatyj\Contracts\module\ProvidesMigrations;
use Besnovatyj\Contracts\module\ProvidesOptions;
use Yii;

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
 * ```
 *
 * URL-правила регистрируются в Bootstrap (L2: вклад 'bootstrap' в config-plugin `common` —
 * выполняется только у активного модуля, гейт modman).
 * DI-привязки регистрируются здесь в init() — только когда модуль создаётся
 * (т.е. URL матчится на маршрут блога). Это lazy loading.
 *
 * @property-read string $controllerNamespace
 */
class Module  extends CmsModule  implements
    DeclaresModule, ProvidesBootstrap, ProvidesDependencies,
    ProvidesDirectories, ProvidesMigrations, ProvidesOptions
{
    public const bool EDITABLE = true;
    public const string VERSION = '1.0.0';
    public const string MODULE_ID = 'BlogNew';

    /**
     * Инициализация модуля.
     *
     * Вызывается только когда URL матчится на маршрут модуля.
     * Регистрируем DI-привязки и определяем контекст приложения.
     *
     * Принцип: модуль сам знает, как ему работать в разных контекстах —
     * приложению не нужно об этом думать (Low Coupling, GRASP).
     */
    public function init(): void
    {
        parent::init();

        // TODO - `setContainerConfig()`
        $this->registerDependencies();
        $this->registerTranslations();
    }

    public static function moduleId(): string { return self::MODULE_ID; }
    public static function moduleVersion(): string { return self::VERSION; }
    public static function isEditable(): bool { return self::EDITABLE; }
    public static function moduleConfig(): array { return require __DIR__.'/config/config.php'; }
    public static function options(): array { return require __DIR__.'/config/options.php'; }
    public static function dependencies(): array { return require __DIR__.'/config/dependencies.php'; }
    public static function migrationPath(): string { return __DIR__.'/migrations'; }
    public static function migrationNamespace(): ?string { return __NAMESPACE__.'\\migrations'; }
    public static function directories(): array { return ['@static/origin/BlogNew','@static/cache/BlogNew'];}
    public static function bootstrapClasses(): array { return [Bootstrap::class]; }


    /**
     * TODO - `config/container.php`
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
     * Регистрация переводов модуля.
     */
    private function registerTranslations(): void
    {
        // TODO: Yii::$app->i18n->translations['blog*'] = [...]
        if (!isset(Yii::$app->i18n->translations['BlogNew'])) {
            Yii::$app->i18n->translations['BlogNew'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'en',
                'basePath' => '@Besnovatyj/BlogNew/messages'
            ];
        }
    }
}
