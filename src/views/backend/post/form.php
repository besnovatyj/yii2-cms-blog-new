<?php

/**
 * Форма создания/редактирования поста.
 *
 * Единая форма для create и update.
 * Различие определяется по наличию $post (null = создание).
 *
 * @var \yii\web\View $this
 * @var \Besnovatyj\BlogNew\models\Post|null $post
 * @var \Besnovatyj\BlogNew\models\Category[] $categories
 */

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use Besnovatyj\BlogNew\models\Post;

$isUpdate = $post !== null;
$this->title = $isUpdate ? "Редактирование: {$post->title}" : 'Новый пост';
?>

<div class="blog-post-form">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin() ?>

        <?= $form->field($post ?? new Post(), 'title')
            ->textInput(['maxlength' => 255, 'name' => 'Post[title]',
                'value' => $post->title ?? '']) ?>

        <?= $form->field($post ?? new Post(), 'slug')
            ->textInput(['maxlength' => 255, 'name' => 'Post[slug]',
                'value' => $post->slug ?? ''])
            ->hint('Оставьте пустым для автогенерации') ?>

        <?= $form->field($post ?? new Post(), 'category_id')
            ->dropDownList(
                ArrayHelper::map($categories, 'id', 'title'),
                ['prompt' => '— Без категории —', 'name' => 'Post[category_id]',
                 'value' => $post->category_id ?? null]
            ) ?>

        <?= $form->field($post ?? new Post(), 'content')
            ->textarea(['rows' => 15, 'name' => 'Post[content]',
                'value' => $post->content ?? '']) ?>

        <?= $form->field($post ?? new Post(), 'status')
            ->dropDownList(Post::statusLabels(), ['name' => 'Post[status]',
                'value' => $post->status ?? Post::STATUS_DRAFT]) ?>

        <?= $form->field($post ?? new Post(), 'meta_title')
            ->textInput(['maxlength' => 255, 'name' => 'Post[meta_title]',
                'value' => $post->meta_title ?? '']) ?>

        <?= $form->field($post ?? new Post(), 'meta_description')
            ->textarea(['rows' => 3, 'name' => 'Post[meta_description]',
                'value' => $post->meta_description ?? '']) ?>

        <div class="form-group">
            <?= Html::submitButton(
                $isUpdate ? 'Сохранить' : 'Создать',
                ['class' => 'btn btn-primary']
            ) ?>
        </div>

    <?php ActiveForm::end() ?>
</div>
