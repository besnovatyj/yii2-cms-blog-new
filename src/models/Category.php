<?php

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
        return '{{%blog_category}}';
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
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['title'], 'required'],
            [['title', 'slug'], 'string', 'max' => 255],
            [['sort_order'], 'integer'],
            [['is_active'], 'boolean'],
            [['sort_order'], 'default', 'value' => 0],
            [['is_active'], 'default', 'value' => true],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id'         => 'ID',
            'title'      => 'Название',
            'slug'       => 'URL-слаг',
            'sort_order' => 'Порядок сортировки',
            'is_active'  => 'Активна',
            'created_at' => 'Создана',
            'updated_at' => 'Обновлена',
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
