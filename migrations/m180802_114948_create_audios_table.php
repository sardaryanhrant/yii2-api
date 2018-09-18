<?php

use yii\db\Migration;

/**
 * Handles the creation of table `audios`.
 */
class m180802_114948_create_audios_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('audios', [
            'id' => $this->primaryKey(),
            'author_id'=>$this->integer()->notNull(),
            'audio_name'=>$this->string()->notNull(),
            'audio_link_url'=>$this->string()->notNull(),
            'privacy'=>$this->integer()->notNull()->defaultValue(1),
            'created_date'=>$this->timestamp()->defaultValue(date('Y-m-d H:i:s')),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('audios');
    }
}
