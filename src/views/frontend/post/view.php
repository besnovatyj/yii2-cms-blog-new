<?php

/**
 * Просмотр одного поста (фронтенд).
 *
 * @var \yii\web\View $this
 * @var \Besnovatyj\BlogNew\models\Post $post
 */

use yii\helpers\Html;

$this->title = $post->meta_title ?: $post->title;

// SEO мета-теги
if ($post->meta_description) {
    $this->registerMetaTag(['name' => 'description', 'content' => $post->meta_description]);
}
?>

<article class="blog-post-view">
    <h1><?= Html::encode($post->title) ?></h1>

    <div class="post-meta">
        <time datetime="<?= date('c', $post->published_at) ?>">
            <?= Yii::$app->formatter->asDatetime($post->published_at) ?>
        </time>

        <?php if ($post->category): ?>
            | <?= Html::a(
                Html::encode($post->category->title),
                ['category', 'slug' => $post->category->slug]
            ) ?>
        <?php endif ?>
    </div>

    <div class="post-content">
        <?= $post->content ?>
    </div>
</article>
