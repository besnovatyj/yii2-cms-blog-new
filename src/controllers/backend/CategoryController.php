<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\BlogNew\controllers\backend;


use Yii;
use yii\base\Module;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\VarDumper;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use Besnovatyj\BlogNew\contracts\CategoryServiceInterface;
use Besnovatyj\BlogNew\exceptions\BlogModuleException;
use Besnovatyj\BlogNew\exceptions\CategoryNotFoundException;
use Besnovatyj\BlogNew\forms\backend\CategoryForm;
use Besnovatyj\BlogNew\forms\backend\search\CategorySearch;

/**
 * Бэкенд-контроллер для управления категориями блога.
 */
class CategoryController extends \yii\web\Controller
{
    use \common\components\controller\ControllerTrait;

    /**
     * @param string $id ID контроллера
     * @param Module $module Модуль-владелец
     * @param CategoryServiceInterface $categoryService Сервис категорий (inject через DI)
     * @param array $config Конфигурация
     */
    public function __construct(
        string $id,
        Module $module,
        private readonly CategoryServiceInterface $categoryService,
        array $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Список категорий в админке.
     */
    public function actionIndex(): string
    {
        $searchModel = new CategorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider'  => $dataProvider,
        ]);
    }

    /**
     * Создание новой категории.
     */
    public function actionCreate(): string|Response
    {
        $form = new CategoryForm();

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->categoryService->create($form->toDto());

                Yii::$app->session->setFlash('success', 'Категория успешно создана');
                return $this->redirect(['index']);
            } catch (BlogModuleException $e) {
                $this->handleDomainException($e, 'Ошибка');
            }
        }

        return $this->render('form', [
            'model' => $form,
        ]);
    }

    /**
     * Обновление существующей категории.
     *
     * @param int $id ID категории
     * @throws NotFoundHttpException
     */
    public function actionUpdate(int $id): string|Response
    {
        try {
            $category = $this->categoryService->getById($id);
        } catch (CategoryNotFoundException $e) {
            Yii::$app->errorHandler->logException($e);
            if (YII_DEBUG) {
                throw new NotFoundHttpException($e->getMessage(), 0, $e);
            } else {
                throw new NotFoundHttpException('Страница не найдена', 0, $e);
            }
        }

        $form = new CategoryForm($category);

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->categoryService->update($id, $form->toDto());

                Yii::$app->session->setFlash('success', 'Категория успешно обновлена');
                return $this->redirect(['index']);
            } catch (BlogModuleException $e) {
                $this->handleDomainException($e, 'Ошибка');
            }
        }

        return $this->render('form', [
            'model' => $form,
        ]);
    }

    /**
     * Удаление категории.
     *
     * @param int $id ID категории
     * @throws NotFoundHttpException
     */
    public function actionDelete(int $id): Response
    {
        try {
            $this->categoryService->delete($id);
            Yii::$app->session->setFlash('success', 'Категория удалена');
        } catch (CategoryNotFoundException $e) {
            Yii::$app->errorHandler->logException($e);
            if (YII_DEBUG) {
                throw new NotFoundHttpException($e->getMessage(), 0, $e);
            } else {
                throw new NotFoundHttpException('Страница не найдена', 0, $e);
            }
        }

        return $this->redirect(['index']);
    }
}
