<?php
use yii\db\Migration;

class m190706_134453_rick_event extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%rick_event}}', [
            'id' => $this->primaryKey(),
            'event' => $this->string(),
            'data' => $this->text(),
            'status' => $this->smallInteger(2),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
    }
    
    public function safeDown()
    {
        $this->dropTable('rick_event');
    }
}
