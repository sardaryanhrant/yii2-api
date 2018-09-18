<?php

use yii\db\Migration;

/**
 * Handles the creation of table `questions`.
 */
class m180813_133123_create_questions_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('questions', [
            'id' => $this->primaryKey(),
            'author_id'=>$this->integer()->notNull(),
            'title'=>$this->string()->notNull(),
            'post_id'=>$this->integer()->notNull(),
            'vote_count'=>$this->integer()->notNull()->defaultValue(0),
            'created_date'=>$this->timestamp()->notNull()->defaultValue(date('Y-m-d H:i:s'))
        ]);

        $this->createIndex('idx-questions-author_id', 'questions', 'author_id', false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('questions');
    }
}
