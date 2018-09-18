<?php

use yii\db\Migration;

/**
 * Handles the creation of table `albums`.
 */
class m180731_082001_create_albums_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('albums', [
            'id' => $this->primaryKey(),
            'author_id'=>$this->integer(),
            'name'=>$this->string(),
            'description'=>$this->string(),
            'photo_num'=>$this->integer()->defaultValue(0),
            'comm_num'=>$this->integer()->defaultValue(0),
            'cover'=>$this->string(),
            'position'=>$this->integer()->defaultValue(0),
            'can_see'=>$this->string()->defaultValue('1'),
            'can_comment'=>$this->string()->defaultValue('1'),
            'editable'=>$this->integer()->defaultValue(1),
            'created_date'=>$this->timestamp()->defaultValue(date('Y-m-d H:i:s')),
            'updated_date'=>$this->timestamp()->defaultValue(date('Y-m-d H:i:s')),
        ]);

        $this->createIndex('idx-albums_author_id', 'albums', 'author_id', false);


    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('albums');
    }
}
