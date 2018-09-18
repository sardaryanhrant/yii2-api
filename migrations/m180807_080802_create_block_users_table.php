<?php

use yii\db\Migration;

/**
 * Handles the creation of table `block_users`.
 */
class m180807_080802_create_block_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('block_users', [
            'id' => $this->primaryKey(),
            'author_id'=>$this->integer()->notNull(),
            'block_user'=>$this->integer()->notNull(),
            'blocked_by'=>$this->string()->notNull()->defaultValue('user'),
            'created_date'=>$this->timestamp()->notNull()->defaultValue(date('Y-m-d H:i:s'))
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('block_users');
    }
}
