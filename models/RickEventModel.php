<?php
namespace breadhead\rickAnalytics\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class RickEventModel
 * @package breadhead\rickAnalytics\models
 *
 * @property int $id
 * @property string $event_type
 * @property string $data
 * @property int $status
 * @property string $client_id
 * @property string $deal_id
 * @property string $error
 * @property int $created_at
 * @property int $updated_at
 */
class RickEventModel extends ActiveRecord
{
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

    public function rules()
    {
        return [
            [['event_type', 'data', 'client_id', 'deal_id', 'error'], 'string'],
            [['id', 'status', 'created_at', 'updated_at'], 'integer']
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
            'error' => 'Error',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
