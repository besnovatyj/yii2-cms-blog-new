<?php

/**
 * Список постов в админке.
 *
 * @var View $this
 * @var PostSearch $searchModel
 * @var ActiveDataProvider $dataProvider
 * @var Category[] $categories
 */

use Besnovatyj\BlogNew\forms\backend\search\PostSearch;
use Besnovatyj\BlogNew\models\Category;
use yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use Besnovatyj\BlogNew\helpers\PostHelper;
use Besnovatyj\BlogNew\models\Post;
use yii\web\View;

$this->title = 'Управление постами';
?>

<div class="blog-post-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <p><?= Html::a('Создать пост', ['create'], ['class' => 'btn btn-success']) ?></p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            'title',
            [
                'attribute' => 'category_id',
                'value' => fn(Post $model) => $model->category->title ?? '—',
                'filter' => ArrayHelper::map($categories, 'id', 'title'),
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => fn(Post $model) => PostHelper::statusLabel($model),
                'filter' => Post::statusLabels(),
            ],
            [
                'attribute' => 'published_at',
                'format' => 'datetime',
            ],
            [
                'class' => ActionColumn::class,
                'template' => '{update} {delete}',
            ],
        ],
    ]) ?>
</div>
