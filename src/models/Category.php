<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\BlogNew\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Модель категории блога.
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property int $sort_order
 * @property bool $is_active
 * @property int $created_at
 * @property int $updated_at
 *
 * @property-read Post[] $posts
 * @property-read int $postsCount
 */
class Category extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%blog_new_categories}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * Связь с постами.
     */
    public function getPosts(): ActiveQuery
    {
        return $this->hasMany(Post::class, ['category_id' => 'id']);
    }
}
