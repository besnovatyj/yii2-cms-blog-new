<?php

/**
 * Список постов блога (фронтенд).
 *
 * @var \yii\web\View $this
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var \Besnovatyj\BlogNew\models\Category[] $categories
 */

use yii\helpers\Html;
use yii\widgets\ListView;

$this->title = 'Блог';
?>

<div class="blog-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= ListView::widget([
        'dataProvider' => $dataProvider,
        'itemView' => function ($model) {
            /** @var \Besnovatyj\BlogNew\models\Post $model */
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
