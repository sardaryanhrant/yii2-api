<?php

use yii\db\Migration;

/**
 * Handles the creation of table `likes`.
 */
class m190725_071120_create_likes_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('likes', [
            'id' => $this->primaryKey(),
            'user_id'=>$this->integer()->notNull(),
            'author_id'=>$this->integer()->notNull(),
            'post_id'=>$this->integer()->notNull(),
            'created_date'=>$this->timestamp()->defaultValue(date('Y-m-d H:i:s'))
        ]);

        $this->createIndex('idx-likes_post_id', 'likes', 'post_id', false);
        $this->createIndex('idx-likes_user_id', 'likes', 'user_id', false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('likes');
    }
}
