<?php

use yii\db\Migration;

/**
 * Handles the creation of table `vote`.
 */
class m180813_133138_create_votes_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('votes', [
            'id' => $this->primaryKey(),
            'author_id'=>$this->integer()->notNull(),
            'question_id'=>$this->integer()->notNull()
        ]);

        $this->createIndex('idx-votes-author_id', 'votes', 'author_id', false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('vote');
    }
}
