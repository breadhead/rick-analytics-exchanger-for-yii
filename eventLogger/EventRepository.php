<?php
namespace breadhead\rickAnalytics\eventLogger;


use yii\db\ActiveRecord;

class EventRepository implements EventRepositoryInterface
{
    private $modelClass;

    public function __construct(string $className)
    {
        if (!class_exists($className)) {
            throw new EventRepositoryException(sprintf('Unknown class name %s was given', $className));
        }

        $this->modelClass = $className;
    }

    public function insert(Event $event): void
    {
        /** @var ActiveRecord $model */
        $model = new $this->modelClass();

        $this->save(
            $model,
            [
                'event_type' => $event->getEventType(),
                'data' => $event->getData(),
                'status' => $event->getStatus(),
                'client_id' => $event->getClientId(),
                'deal_id' => $event->getDealId(),
            ]
        );
    }

    public function update(Event $event): void
    {
        if (!$event->getId()) {
            throw new EventRepositoryException('Event Id must be set to update');
        }

        /** @var ActiveRecord $model */
        $model = $this->modelClass->findOne($event->getId());

        if (!$model) {
            throw new EventRepositoryException(sprintf('Model with such id %s was not found', $event->getId()));
        }

        $this->save(
            $model,
            [
                'event_type' => $event->getEventType(),
                'data' => $event->getData(),
                'status' => $event->getStatus(),
                'client_id' => $event->getClientId(),
                'deal_id' => $event->getDealId(),
            ]
        );
    }

    private function save(ActiveRecord $model, array $fields): int
    {
        $model->setAttributes($fields);

        $res = $model->save();

        if (!$res) {
            throw new EventRepositoryException(json_decode($model->getErrors()));
        }

        return $model->id;
    }

    public function find(array $filter, int $limit = 5): ?array
    {
        $models = $this->modelClass->find()
            ->where($filter)
            ->limit($limit)
            ->orderBy(['id' => SORT_ASC])
            ->all();

        if (empty($models)) {
            return null;
        }

        $result = [];
        array_map(
            function($model) use (&$result) {
                $result[] = $this->convertModelToEvent($model);
            },
            $models
        );
    }

    public function findOne(array $filter): ?Event
    {
        /** @var ActiveRecord $model */
        $model = $this->modelClass->find()
            ->where($filter)
            ->one();

        if (!$model) {
            return null;
        }

        return $this->convertModelToEvent($model);
    }

    private function convertModelToEvent(ActiveRecord $model): Event
    {
        return new Event(
            $model->id,
            $model->event_type,
            json_decode($model->data, true),
            $model->client_id,
            $model->deal_id,
            $model->status,
            $model->created_at,
            $model->updated_at
        );
    }

    public function isDealCreated(string $dealId)
    {
        return $this->findOne(['deal_id' => $dealId, 'event_type' => Event::TYPE_CREATE]) ? true : false;
    }
}
