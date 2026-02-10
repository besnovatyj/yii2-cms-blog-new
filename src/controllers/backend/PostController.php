<?php

declare(strict_types=1);

namespace Besnovatyj\BlogNew\controllers\backend;

use Yii;
use yii\base\Module;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use Besnovatyj\BlogNew\contracts\PostServiceInterface;
use Besnovatyj\BlogNew\contracts\CategoryServiceInterface;
use Besnovatyj\BlogNew\exceptions\BlogModuleException;
use Besnovatyj\BlogNew\exceptions\PostNotFoundException;
use Besnovatyj\BlogNew\forms\backend\PostForm;
use Besnovatyj\BlogNew\forms\backend\search\PostSearch;

/**
 * Бэкенд-контроллер для управления постами (админка).
 *
 * КЛЮЧЕВОЙ ПРИНЦИП: контроллер — ТОНКИЙ.
 *
 * Что делает контроллер:
 * 1. Принимает HTTP-запрос
 * 2. Загружает данные в форму и валидирует
 * 3. Конвертирует форму в DTO
 * 4. Вызывает метод сервиса
 * 5. Обрабатывает результат/ошибку
 * 6. Возвращает ответ (рендер вьюхи или редирект)
 *
 * Зависимости через конструктор (Constructor Injection):
 * Yii2 DI-контейнер автоматически передаёт PostServiceInterface,
 * потому что мы зарегистрировали привязку в Module::init().
 */
class PostController extends Controller
{
    /**
     * @param string $id ID контроллера
     * @param Module $module Модуль-владелец
     * @param PostServiceInterface $postService Сервис постов (inject через DI)
     * @param CategoryServiceInterface $categoryService Сервис категорий (для списков)
     * @param array $config Конфигурация
     */
    public function __construct(
        string $id,
        Module $module,
        private readonly PostServiceInterface $postService,
        private readonly CategoryServiceInterface $categoryService,
        array $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * Фильтры поведения контроллера.
     *
     * Это инфраструктурная забота контроллера — он знает о HTTP:
     * - Какие действия требуют авторизации
     * - Какие HTTP-методы допустимы
     */
    public function behaviors(): array
    {
        return [
            // Контроль доступа: только авторизованные пользователи
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Только залогиненные
                    ],
                ],
            ],

            // Ограничение HTTP-методов
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete'    => ['POST'],
                    'publish'   => ['POST'],
                    'unpublish' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Список постов в админке.
     *
     * PostSearch — специализированная форма фильтрации для GridView.
     * Она сама строит запрос с фильтрами и возвращает ActiveDataProvider.
     */
    public function actionIndex(): string
    {
        $searchModel = new PostSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider'  => $dataProvider,
            'categories'    => $this->categoryService->getActiveList(),
        ]);
    }

    /**
     * Создание нового поста.
     *
     * Паттерн:
     * 1. GET-запрос → показываем пустую форму
     * 2. POST-запрос → загружаем данные в форму → валидируем → создаём DTO → сервис
     * 3. Успех → редирект на список
     * 4. Ошибка валидации формы → показываем форму с ошибками
     * 5. Ошибка бизнес-логики (сервис) → flash-сообщение + форма
     */
    public function actionCreate(): string|Response
    {
        $form = new PostForm();

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->postService->create($form->toCreateDto());

                Yii::$app->session->setFlash('success', 'Пост успешно создан');
                return $this->redirect(['index']);
            } catch (BlogModuleException $e) {
                // Маппинг доменного исключения → пользовательское сообщение.
                // Сервис не знает про flash-сообщения — это забота контроллера.
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('form', [
            'model'      => $form,
            'categories' => $this->categoryService->getActiveList(),
        ]);
    }

    /**
     * Обновление существующего поста.
     *
     * @param int $id ID поста
     * @throws NotFoundHttpException
     */
    public function actionUpdate(int $id): string|Response
    {
        try {
            // Получаем пост для отображения в форме
            $post = $this->postService->getById($id);
        } catch (PostNotFoundException $e) {
            // Маппинг: доменное исключение "не найден" → HTTP 404.
            // Это единственное место, где домен "касается" HTTP.
            throw new NotFoundHttpException($e->getMessage(), 0, $e);
        }

        $form = new PostForm($post);

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->postService->update($id, $form->toUpdateDto());

                Yii::$app->session->setFlash('success', 'Пост успешно обновлён');
                return $this->redirect(['index']);
            } catch (BlogModuleException $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('form', [
            'model'      => $form,
            'categories' => $this->categoryService->getActiveList(),
        ]);
    }

    /**
     * Удаление поста.
     *
     * @param int $id ID поста
     * @throws NotFoundHttpException
     */
    public function actionDelete(int $id): Response
    {
        try {
            $this->postService->delete($id);
            Yii::$app->session->setFlash('success', 'Пост удалён');
        } catch (PostNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), 0, $e);
        }

        return $this->redirect(['index']);
    }

    /**
     * Публикация поста.
     *
     * Отдельный action, а не параметр в update, потому что
     * публикация — самостоятельное бизнес-действие (не просто смена поля).
     *
     * @param int $id ID поста
     * @throws NotFoundHttpException
     */
    public function actionPublish(int $id): Response
    {
        try {
            $this->postService->publish($id);
            Yii::$app->session->setFlash('success', 'Пост опубликован');
        } catch (PostNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), 0, $e);
        }

        return $this->redirect(['index']);
    }

    /**
     * Снятие поста с публикации.
     *
     * @param int $id ID поста
     * @throws NotFoundHttpException
     */
    public function actionUnpublish(int $id): Response
    {
        try {
            $this->postService->unpublish($id);
            Yii::$app->session->setFlash('success', 'Пост снят с публикации');
        } catch (PostNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), 0, $e);
        }

        return $this->redirect(['index']);
    }
}
