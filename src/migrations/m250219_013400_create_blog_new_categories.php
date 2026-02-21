<?php

namespace Besnovatyj\BlogNew\migrations;

use common\components\migration\BaseMigration;
use yii\base\NotSupportedException;

/** 'm<YYMMDD_HHMMSS>_<Name>' */
class m250219_013400_create_blog_new_categories extends BaseMigration
{
    public const string TABLE_NAME = '{{%blog_new_categories}}';

    /**
     * @throws NotSupportedException
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
                ->comment('Название категории'),
            'slug' => $this->string(255)->notNull()->unique()
                ->comment('Slug категории'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0)
                ->comment('Сортировка категорий'),
            'is_active' => $this->boolean()->notNull()->defaultValue(true)
                ->comment('Статус активности категории'),
            'created_at' => $this->integer()->notNull()
                ->comment('Дата создания'),
            'updated_at' => $this->integer()->notNull()
                ->comment('Дата последнего редактирования'),
        ]);
        $this->addCommentOnTable(static::TABLE_NAME, 'Категории постов блога');

        $this->createIndexes(static::TABLE_NAME, 'slug', false, true);
        $this->createIndexes(static::TABLE_NAME, 'is_active');
    }

    public function safeDown(): void
    {
        parent::safeDown();
    }
}
