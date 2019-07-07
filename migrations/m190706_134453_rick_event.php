<?php
use yii\db\Migration;

class m190706_134453_rick_event extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%rick_event}}', [
            'id' => $this->primaryKey(),
            'event_type' => $this->string(),
            'data' => $this->text(),
            'client_id' => $this->string(),
            'deal_id' => $this->string(),
            'status' => $this->smallInteger(2),
            'error' => $this->text(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx-rick_event-event', '{{%rick_event}}', 'event_type');
        $this->createIndex('idx-rick_event-status', '{{%rick_event}}', 'status');
        $this->createIndex('idx-rick_event-client_id', '{{%rick_event}}', 'client_id');
        $this->createIndex('idx-rick_event-deal_id', '{{%rick_event}}', 'deal_id');
    }
    
    public function safeDown()
    {
        $this->dropTable('rick_event');
    }
}
