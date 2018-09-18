<?php

use yii\db\Migration;

/**
 * Handles the creation of table `messages`.
 */
class m180714_102858_create_messages_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('messages', [
            'id' => $this->primaryKey(),
            'author_id'=>$this->integer(),
            'from_id'=>$this->integer()->notNull(),
            'for_id'=>$this->integer()->notNull(),
            'content'=>$this->string(), 
            'read_status'=>$this->integer()->notNull()->defaultValue(0),
            'created_at'=>$this->timestamp()->notNull()->defaultValue(date('Y-m-d H:i:s')),
//            'attachment_id'=>$this->integer(),
//            'attachment_src'=>$this->string(),
            'chat_id'=> $this->integer()->notNull(),    
        ]);


        $this->createIndex('idx-chat_id', 'messages', 'chat_id', false);       
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('messages');
    }
}
