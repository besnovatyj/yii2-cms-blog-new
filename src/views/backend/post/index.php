<?php

/**
 * Список постов в админке.
 *
 * @var \yii\web\View $this
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var \Besnovatyj\BlogNew\models\Category[] $categories
 */

use yii\grid\GridView;
use yii\helpers\Html;
use Besnovatyj\BlogNew\helpers\PostHelper;
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
                'format' => 'raw',
                'value' => fn(Post $model) => PostHelper::statusLabel($model),
            ],
            [
                'attribute' => 'published_at',
                'format' => 'datetime',
            ],
            [
                'class' => \yii\grid\ActionColumn::class,
                'template' => '{update} {delete}',
            ],
        ],
    ]) ?>
</div>
