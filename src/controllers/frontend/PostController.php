<?php

declare(strict_types=1);

namespace Besnovatyj\BlogNew\controllers\frontend;

use yii\web\Controller;
use yii\web\NotFoundHttpException;
use Besnovatyj\BlogNew\contracts\PostServiceInterface;
use Besnovatyj\BlogNew\contracts\CategoryServiceInterface;
use Besnovatyj\BlogNew\exceptions\PostNotFoundException;
use Besnovatyj\BlogNew\exceptions\CategoryNotFoundException;

/**
 * Фронтенд-контроллер блога (для посетителей сайта).
 *
 * Ключевое отличие от бэкенд-контроллера:
 * 1. Только чтение — никаких create/update/delete
 * 2. Работает только с опубликованными постами
 * 3. Не требует авторизации (по умолчанию)
 * 4. Оптимизирован для SEO (мета-теги, красивые URL)
 *
 * Принцип ISP (Interface Segregation):
 * Фронтенд и бэкенд — разные клиенты сервиса. Они используют
 * разные методы интерфейса PostServiceInterface. Если бы их наборы
 * сильно отличались, стоило бы разделить интерфейс на два.
 */
class PostController extends Controller
{
    public function __construct(
        string $id,
        \yii\base\Module $module,
        private readonly PostServiceInterface $postService,
        private readonly CategoryServiceInterface $categoryService,
        array $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * Список опубликованных постов (главная страница блога).
     */
    public function actionIndex(): string
    {
        $dataProvider = $this->postService->getPublishedList();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'categories'   => $this->categoryService->getActiveList(),
        ]);
    }

    /**
     * Просмотр одного поста по слагу.
     *
     * Сервис сам проверит, что пост опубликован.
     * Контроллер просто ловит исключение и конвертит в 404.
     *
     * @param string $slug URL-слаг поста
     */
    public function actionView(string $slug): string
    {
        try {
            $post = $this->postService->getPublishedBySlug($slug);
        } catch (PostNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), 0, $e);
        }

        return $this->render('view', [
            'post' => $post,
        ]);
    }

    /**
     * Посты по категории.
     *
     * @param string $slug Слаг категории
     */
    public function actionCategory(string $slug): string
    {
        try {
            $dataProvider = $this->postService->getPublishedByCategory($slug);
        } catch (CategoryNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), 0, $e);
        }

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'categories'   => $this->categoryService->getActiveList(),
        ]);
    }
}
