<?php

use yii\db\Migration;

/**
 * Handles the creation of table `taxonomies`.
 */
class m180725_113229_create_taxonomies_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('taxonomies', [
            'id' => $this->primaryKey(),
            'tax_name'=>$this->string()->notNull(),
            'tax_slug'=>$this->string()->notNull(),
            'tax_for'=>$this->string(),
            'created_date'=>$this->timestamp()->defaultValue(date('Y-m-d H:i:s'))
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('taxonomies');
    }
}
