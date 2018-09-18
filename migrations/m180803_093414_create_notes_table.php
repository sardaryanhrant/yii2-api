<?php

use yii\db\Migration;

/**
 * Handles the creation of table `notes`.
 */
class m180803_093414_create_notes_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('notes', [
            'id' => $this->primaryKey(),
            'author_id'=>$this->integer()->notNull(),
            'name'=>$this->string()->notNull(),
            'content'=>$this->string(2000),
            'show_on_wall'=>$this->integer()->notNull()->defaultValue(0),
            'compliant'=>$this->string(),
            'created_date'=>$this->timestamp()->notNull()->defaultValue(date('Y-m-d H:i:s'))
        ]);
        $this->createIndex('idx-notes-author_id', 'notes', 'author_id', false);


    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('notes');
    }
}
