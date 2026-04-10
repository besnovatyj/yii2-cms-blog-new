<?php

use Besnovatyj\BlogNew\forms\backend\search\CategorySearch;
use yii\data\ActiveDataProvider;
use backend\widgets\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use Besnovatyj\BlogNew\helpers\CategoryHelper;
use Besnovatyj\BlogNew\models\Category;
use yii\web\View;

/**
 * Список категорий в админке.
 *
 * @var View $this
 * @var CategorySearch $searchModel
 * @var ActiveDataProvider $dataProvider
 */

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
                'class' => ActionColumn::class,
                'template' => '{update} {delete}',
            ],
        ],
    ]) ?>
</div>
