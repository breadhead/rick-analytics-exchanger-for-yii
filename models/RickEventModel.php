<?php
namespace breadhead\rickAnalytics\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class RickEventModel
 * @package breadhead\rickAnalytics\models
 *
 * @var int $id
 * @var string $event_type
 * @var string $data
 * @var int $status
 * @var string $client_id
 * @var string $deal_id
 * @var int $created_at
 * @var int $updated_at
 */
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
            TimestampBehavior::class,
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'event_type' => 'Event Type',
            'data' => 'Data',
            'status' => 'Status',
            'client_id' => 'Client ID',
            'deal_id' => 'Deal ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
