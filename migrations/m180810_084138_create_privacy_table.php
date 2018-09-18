<?php

use yii\db\Migration;

/**
 * Handles the creation of table `privacy`.
 */
class m180810_084138_create_privacy_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('privacy', [
            'id' => $this->primaryKey(),
            'author_id'=>$this->integer()->notNull(),
            'write_messages'=>$this->integer()->notNull()->defaultValue(1),
            'sees_other_records'=>$this->integer()->notNull()->defaultValue(1),
            'can_post'=>$this->integer()->notNull()->defaultValue(1),
            'can_comment'=>$this->integer()->notNull()->defaultValue(1),
            'basic_info'=>$this->integer()->notNull()->defaultValue(1),
            'see_guests'=>$this->integer()->notNull()->defaultValue(1),
            'created_date'=>$this->timestamp()->notNull()->defaultValue(date('Y-m-d H:i:s')),
        ]);

        $this->createIndex('idx-privacy-author_id', 'privacy', 'author_id', false);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('privacy');
    }
}
