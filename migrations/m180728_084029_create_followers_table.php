<?php

use yii\db\Migration;

/**
 * Handles the creation of table `followers`.
 */
class m180728_084029_create_followers_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('followers', [
            'id' => $this->primaryKey(),
            'author_id'=>$this->integer()->notNull(),
            'user_id'=>$this->integer()->notNull(),
            'follow_to'=>$this->integer()->notNull(),
            'to'=>$this->string(),
            'created_date'=>$this->timestamp()->defaultValue(date('Y-m-d H:i:s'))
        ]);
        $this->createIndex('idx-followers_user_id', 'followers', 'user_id', false);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('followers');
    }
}
