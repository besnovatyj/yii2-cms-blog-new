# Модуль блога для Yii2 — Архитектура "Controller → Service → Repository"

## Структура модуля

```
blog/
├── Module.php                          # Точка входа: DI-привязки, маршруты
│
├── contracts/                          # 🔌 Интерфейсы (DIP — Dependency Inversion)
│   ├── PostRepositoryInterface.php     #    Контракт слоя данных
│   ├── CategoryRepositoryInterface.php
│   ├── PostServiceInterface.php        #    Контракт бизнес-логики
│   └── CategoryServiceInterface.php
│
├── dto/                                # 📦 Data Transfer Objects
│   ├── PostCreateDto.php               #    Данные для создания поста
│   ├── PostUpdateDto.php               #    Данные для обновления поста
│   └── CategoryDto.php                 #    Данные для категории
│
├── models/                             # 🗄️ ActiveRecord-модели (чистые, без логики)
│   ├── Post.php                        #    Описание сущности, связи, валидация полей
│   └── Category.php
│
├── repositories/                       # 💾 Слой доступа к данным
│   ├── PostRepository.php              #    CRUD + выборки через ActiveQuery
│   └── CategoryRepository.php
│
├── services/                           # ⚙️ Бизнес-логика
│   ├── PostService.php                 #    Создание, публикация, валидация правил
│   └── CategoryService.php
│
├── exceptions/                         # ❌ Доменные исключения
│   ├── BlogModuleException.php         #    Базовое исключение модуля
│   ├── PostNotFoundException.php
│   ├── PostCreateException.php
│   ├── PostUpdateException.php
│   └── CategoryNotFoundException.php
│
├── events/                             # 📡 Доменные события (OCP — расширяемость)
│   └── PostEvent.php                   #    afterCreate, afterPublish, etc.
│
├── controllers/
│   ├── backend/
│   │   └── PostController.php          # 🖥️ Админка: CRUD + публикация
│   └── frontend/
│       └── PostController.php          # 🌐 Сайт: список, просмотр, по категории
│
├── views/                              # 👁️ Минимальные представления
│   ├── backend/post/
│   │   ├── index.php
│   │   └── form.php
│   └── frontend/post/
│       ├── index.php
│       └── view.php
│
└── migrations/
    └── m240101_000000_create_blog_tables.php
```

## Поток данных

```
HTTP-запрос
    │
    ▼
┌─────────────┐   DTO    ┌──────────┐   Model    ┌──────────────┐   SQL
│ Controller  │ ───────▶ │ Service  │ ─────────▶ │ Repository   │ ─────▶ БД
│ (тонкий)    │          │ (логика) │            │ (данные)     │
└─────────────┘          └──────────┘            └──────────────┘
    │                         │
    │ renders                 │ triggers
    ▼                         ▼
┌─────────┐             ┌──────────┐
│  View   │             │  Event   │ → подписчики (email, кэш, etc.)
└─────────┘             └──────────┘
```

## Принципы SOLID

| Принцип                       | Как реализован                                                 |
|-------------------------------|----------------------------------------------------------------|
| **S** — Single Responsibility | Контроллер = HTTP, Сервис = логика, Репозиторий = данные       |
| **O** — Open/Closed           | Доменные события позволяют расширять без модификации           |
| **L** — Liskov Substitution   | Любая реализация интерфейса репозитория взаимозаменяема        |
| **I** — Interface Segregation | Отдельные интерфейсы для Post/Category, для Service/Repository |
| **D** — Dependency Inversion  | Зависимости на интерфейсы, привязки в DI-контейнере            |

## Принципы GRASP

| Принцип                  | Как реализован                                                    |
|--------------------------|-------------------------------------------------------------------|
| **Information Expert**   | Репозиторий — эксперт по данным, Сервис — по бизнес-правилам      |
| **Creator**              | Сервис создаёт модели, DI-контейнер создаёт сервисы               |
| **Low Coupling**         | Слои связаны через интерфейсы, модуль самодостаточен              |
| **High Cohesion**        | Каждый класс делает одно дело и делает его хорошо                 |
| **Controller**           | Тонкий контроллер — только приём запроса и вызов сервиса          |
| **Polymorphism**         | Подмена реализации репозитория (AR → Elasticsearch) без изменений |
| **Indirection**          | DTO разделяет HTTP-слой и доменный слой                           |
| **Protected Variations** | Интерфейсы защищают от изменений реализации                       |

## Подключение к проекту

```php
// common/config/main.php
'modules' => [
    'blog' => [
        'class' => \modules\blog\Module::class,
    ],
],
'bootstrap' => ['blog'],

// console/config/main.php (для миграций)
'controllerMap' => [
    'migrate' => [
        'class' => 'yii\console\controllers\MigrateController',
        'migrationNamespaces' => [
            'modules\blog\migrations',
        ],
    ],
],
```

## Тестирование

Благодаря DIP, сервисы легко тестировать:

```php
class PostServiceTest extends TestCase
{
    public function testCreatePostGeneratesSlug(): void
    {
        // Мокаем репозиторий — БД не нужна
        $postRepo = $this->createMock(PostRepositoryInterface::class);
        $postRepo->method('existsBySlug')->willReturn(false);
        $postRepo->method('save')->willReturn(true);

        $categoryRepo = $this->createMock(CategoryRepositoryInterface::class);

        $service = new PostService($postRepo, $categoryRepo);

        $dto = new PostCreateDto(title: 'Мой первый пост', content: 'Текст');
        $post = $service->create($dto);

        $this->assertEquals('moj-pervyj-post', $post->slug);
    }
}
```
