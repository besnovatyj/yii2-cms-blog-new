<?php

/**
 * Форма создания/редактирования категории.
 *
 * @var \yii\web\View $this
 * @var \Besnovatyj\BlogNew\forms\backend\CategoryForm $model
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$isUpdate = $model->title !== null;
$this->title = $isUpdate ? "Редактирование: {$model->title}" : 'Новая категория';
?>

<div class="blog-category-form">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin() ?>

        <?= $form->field($model, 'title')->textInput(['maxlength' => 255]) ?>

        <?= $form->field($model, 'slug')
            ->textInput(['maxlength' => 255])
            ->hint('Оставьте пустым для автогенерации') ?>

        <?= $form->field($model, 'sort_order')->textInput(['type' => 'number']) ?>

        <?= $form->field($model, 'is_active')->checkbox() ?>

        <div class="form-group">
            <?= Html::submitButton(
                $isUpdate ? 'Сохранить' : 'Создать',
                ['class' => 'btn btn-primary']
            ) ?>
        </div>

    <?php ActiveForm::end() ?>
</div>
