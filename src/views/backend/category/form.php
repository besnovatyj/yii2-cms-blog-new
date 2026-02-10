<?php

use Besnovatyj\BlogNew\forms\backend\CategoryForm;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/**
 * Форма создания/редактирования категории.
 *
 * @var View $this
 * @var CategoryForm $model
 */

$isUpdate = $model->title !== null;
$this->title = $isUpdate ? "Редактирование: $model->title" : 'Новая категория';
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
