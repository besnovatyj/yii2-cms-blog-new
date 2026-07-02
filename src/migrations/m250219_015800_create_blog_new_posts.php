<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\BlogNew\migrations;

use Besnovatyj\Kernel\migration\BaseMigration;
use Yii;
use yii\base\NotSupportedException;
use yii\db\Exception;

class m250219_015800_create_blog_new_posts extends BaseMigration
{
    public const string TABLE_NAME = '{{%blog_new_posts}}';

    /**
     * @throws NotSupportedException
     * @throws Exception
     */
    public function safeUp(): void
    {
        parent::safeUp();

        if ($this->existTable(static::TABLE_NAME)) {
            return;
        }

        $this->createTable(static::TABLE_NAME, [
            'id' => $this->primaryKey()
                ->comment('PK'),
            'title' => $this->string(255)->notNull()
                ->comment('Заголовок поста'),
            'slug' => $this->string(255)->notNull()->unique()
                ->comment('Slug поста'),
            'content' => $this->text()->notNull()
                ->comment('Содержимое поста'),
            'excerpt' => $this->text()
                ->comment('Краткое содержимое поста'),
            'category_id' => $this->integer()
                ->comment('Идентификатор категории поста'),
            'author_id' => $this->integer()->notNull()
                ->comment('Идентификатор автора поста'),
            'status' => $this->smallInteger()->notNull()->defaultValue(0)
                ->comment('Статус поста'),
            'meta_title' => $this->string(255)
                ->comment('Meta title'),
            'meta_description' => $this->string(500)
                ->comment('Meta description'),
            'published_at' => $this->integer()
                ->comment('Дата публикации поста'),
            'created_at' => $this->integer()->notNull()
                ->comment('ДАта создания поста'),
            'updated_at' => $this->integer()->notNull()
                ->comment('Дата последнего редактирования'),
        ]);
        $this->addCommentOnTable(static::TABLE_NAME, 'Посты блога');

        // Индексы для типичных запросов
        $this->createIndexes(static::TABLE_NAME, 'slug', false, true);
        $this->createIndexes(static::TABLE_NAME, 'status');
        $this->createIndexes(static::TABLE_NAME, 'category_id');
        $this->createIndexes(static::TABLE_NAME, 'author_id');
        $this->createIndexes(static::TABLE_NAME, 'published_at');

        // Составной индекс для выборки "опубликованные, отсортированные по дате"
        $this->createIndexes(static::TABLE_NAME, ['status', 'published_at']);

        // Внешний ключ: пост → категория (SET NULL при удалении категории)

        Yii::$app->db->createCommand('SET foreign_key_checks = 0')->execute();
        $this->createFKs(
            static::TABLE_NAME,
            'category_id',
            m250219_013400_create_blog_new_categories::TABLE_NAME,
            'id',
            'SET NULL', // При удалении категории — обнуляем, а не удаляем посты
            'CASCADE'
        );
        Yii::$app->getDb()->createCommand("SET foreign_key_checks = 1")->execute();

    }

    public function safeDown(): void
    {
        parent::safeDown();
    }
}
