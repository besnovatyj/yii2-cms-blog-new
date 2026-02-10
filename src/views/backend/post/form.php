<?php

/**
 * Форма создания/редактирования поста.
 *
 * Единая форма для create и update.
 * Различие определяется по заполненности модели.
 *
 * @var \yii\web\View $this
 * @var \Besnovatyj\BlogNew\forms\backend\PostForm $model
 * @var \Besnovatyj\BlogNew\models\Category[] $categories
 */

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use Besnovatyj\BlogNew\models\Post;

$isUpdate = $model->title !== null;
$this->title = $isUpdate ? "Редактирование: {$model->title}" : 'Новый пост';
?>

<div class="blog-post-form">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin() ?>

        <?= $form->field($model, 'title')->textInput(['maxlength' => 255]) ?>

        <?= $form->field($model, 'slug')
            ->textInput(['maxlength' => 255])
            ->hint('Оставьте пустым для автогенерации') ?>

        <?= $form->field($model, 'category_id')
            ->dropDownList(
                ArrayHelper::map($categories, 'id', 'title'),
                ['prompt' => '— Без категории —']
            ) ?>

        <?= $form->field($model, 'content')->textarea(['rows' => 15]) ?>

        <?= $form->field($model, 'status')->dropDownList(Post::statusLabels()) ?>

        <?= $form->field($model, 'meta_title')->textInput(['maxlength' => 255]) ?>

        <?= $form->field($model, 'meta_description')->textarea(['rows' => 3]) ?>

        <div class="form-group">
            <?= Html::submitButton(
                $isUpdate ? 'Сохранить' : 'Создать',
                ['class' => 'btn btn-primary']
            ) ?>
        </div>

    <?php ActiveForm::end() ?>
</div>
