<?php

use yii\db\Migration;

/**
 * Handles the creation of table `videos`.
 */
class m180802_105003_create_videos_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('videos', [
            'id' => $this->primaryKey(),
            'author_id'=>$this->integer()->notNull(),
            'video_name'=>$this->string()->notNull(),
            'video_image'=>$this->string(),
            'link_to_videos'=>$this->string()->notNull(),
            'video_description'=>$this->string(),
            'privacy'=>$this->integer()->notNull()->defaultValue(1),
            'created_date'=>$this->timestamp()->defaultValue(date('Y-m-d H:i:s')),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('videos');

    }
}
