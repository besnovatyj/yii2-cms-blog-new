<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Миграция: создание таблиц модуля блога.
 *
 * Миграция — часть модуля, а не приложения.
 * Подключается через migrationNamespaces в конфиге:
 *
 * ```php
 * 'controllerMap' => [
 *     'migrate' => [
 *         'class' => 'yii\console\controllers\MigrateController',
 *         'migrationNamespaces' => [
 *             'Besnovatyj\BlogNew\migrations',
 *         ],
 *     ],
 * ],
 * ```
 */
class m240101_000000_create_blog_tables extends Migration
{
    public function safeUp(): void
    {
        // ── Таблица категорий ─────────────────────────────────────
        $this->createTable('{{%blog_category}}', [
            'id'         => $this->primaryKey(),
            'title'      => $this->string(255)->notNull(),
            'slug'       => $this->string(255)->notNull()->unique(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'is_active'  => $this->boolean()->notNull()->defaultValue(true),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx-blog_category-slug', '{{%blog_category}}', 'slug', true);
        $this->createIndex('idx-blog_category-is_active', '{{%blog_category}}', 'is_active');

        // ── Таблица постов ────────────────────────────────────────
        $this->createTable('{{%blog_post}}', [
            'id'               => $this->primaryKey(),
            'title'            => $this->string(255)->notNull(),
            'slug'             => $this->string(255)->notNull()->unique(),
            'content'          => $this->text()->notNull(),
            'excerpt'          => $this->text(),
            'category_id'      => $this->integer(),
            'author_id'        => $this->integer()->notNull(),
            'status'           => $this->smallInteger()->notNull()->defaultValue(0),
            'meta_title'       => $this->string(255),
            'meta_description' => $this->string(500),
            'published_at'     => $this->integer(),
            'created_at'       => $this->integer()->notNull(),
            'updated_at'       => $this->integer()->notNull(),
        ]);

        // Индексы для типичных запросов
        $this->createIndex('idx-blog_post-slug', '{{%blog_post}}', 'slug', true);
        $this->createIndex('idx-blog_post-status', '{{%blog_post}}', 'status');
        $this->createIndex('idx-blog_post-category_id', '{{%blog_post}}', 'category_id');
        $this->createIndex('idx-blog_post-author_id', '{{%blog_post}}', 'author_id');
        $this->createIndex('idx-blog_post-published_at', '{{%blog_post}}', 'published_at');

        // Составной индекс для выборки "опубликованные, отсортированные по дате"
        $this->createIndex(
            'idx-blog_post-status-published_at',
            '{{%blog_post}}',
            ['status', 'published_at']
        );

        // Внешний ключ: пост → категория (SET NULL при удалении категории)
        $this->addForeignKey(
            'fk-blog_post-category_id',
            '{{%blog_post}}',
            'category_id',
            '{{%blog_category}}',
            'id',
            'SET NULL', // При удалении категории — обнуляем, а не удаляем посты
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%blog_post}}');
        $this->dropTable('{{%blog_category}}');
    }
}
