<?php

/**
 * Список постов в админке.
 *
 * Вьюха НЕ содержит логики — только отображение данных,
 * которые подготовил контроллер. Это "V" в MVC.
 *
 * @var \yii\web\View $this
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var \Besnovatyj\BlogNew\models\Category[] $categories
 */

use yii\grid\GridView;
use yii\helpers\Html;
use Besnovatyj\BlogNew\models\Post;

$this->title = 'Управление постами';
?>

<div class="blog-post-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <p><?= Html::a('Создать пост', ['create'], ['class' => 'btn btn-success']) ?></p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'title',
            [
                'attribute' => 'category_id',
                'value' => fn(Post $model) => $model->category->title ?? '—',
            ],
            [
                'attribute' => 'status',
                'value' => fn(Post $model) => Post::statusLabels()[$model->status] ?? '?',
            ],
            [
                'attribute' => 'published_at',
                'format' => 'datetime',
            ],
            [
                'class' => \yii\grid\ActionColumn::class,
                'template' => '{update} {publish} {delete}',
                'buttons' => [
                    'publish' => function ($url, Post $model) {
                        if ($model->isPublished()) {
                            return Html::a('Снять', ['unpublish', 'id' => $model->id], [
                                'data-method' => 'post',
                            ]);
                        }
                        return Html::a('Опубликовать', ['publish', 'id' => $model->id], [
                            'data-method' => 'post',
                        ]);
                    },
                ],
            ],
        ],
    ]) ?>
</div>
