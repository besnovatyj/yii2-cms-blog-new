<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/**
 * Список постов блога (фронтенд).
 *
 * @var View $this
 * @var ActiveDataProvider $dataProvider
 * @var Category[] $categories
 */

use Besnovatyj\BlogNew\models\Category;
use Besnovatyj\BlogNew\models\Post;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ListView;

$this->title = 'Блог';
?>

<div class="blog-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= ListView::widget([
        'dataProvider' => $dataProvider,
        'itemView' => function ($model) {
            /** @var Post $model */
            return Html::tag('article', implode("\n", [
                Html::tag('h2', Html::a(
                    Html::encode($model->title),
                    ['view', 'slug' => $model->slug]
                )),
                Html::tag('p', Html::encode($model->excerpt)),
                Html::tag('small', Yii::$app->formatter->asDatetime($model->published_at)),
            ]));
        },
        'layout' => "{items}\n{pager}",
    ]) ?>
</div>
