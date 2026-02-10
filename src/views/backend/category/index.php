<?php

/**
 * Список категорий в админке.
 *
 * @var \yii\web\View $this
 * @var \Besnovatyj\BlogNew\forms\backend\search\CategorySearch $searchModel
 * @var \yii\data\ActiveDataProvider $dataProvider
 */

use yii\grid\GridView;
use yii\helpers\Html;
use Besnovatyj\BlogNew\helpers\CategoryHelper;
use Besnovatyj\BlogNew\models\Category;

$this->title = 'Управление категориями';
?>

<div class="blog-category-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <p><?= Html::a('Создать категорию', ['create'], ['class' => 'btn btn-success']) ?></p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            'title',
            'slug',
            'sort_order',
            [
                'attribute' => 'is_active',
                'format' => 'raw',
                'value' => fn(Category $model) => CategoryHelper::activeBadge($model),
                'filter' => CategoryHelper::activeList(),
            ],
            [
                'class' => \yii\grid\ActionColumn::class,
                'template' => '{update} {delete}',
            ],
        ],
    ]) ?>
</div>
