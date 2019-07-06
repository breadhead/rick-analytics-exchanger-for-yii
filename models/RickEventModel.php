<?php
namespace breadhead\rickAnalytics\models;

use yii\db\ActiveRecord;

class RickEventModel extends ActiveRecord
{
    const NEW = 10;
    const IN_PROGRESS = 5;
    const DONE = 0;

    public static function tableName()
    {
        return 'rick_event';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'event_type' => 'Event ID',
            'data' => 'Data',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
