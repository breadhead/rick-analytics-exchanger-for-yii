<?php
namespace breadhead\rickAnalytics\api\entities;

class Deal
{
    public $orderId;
    public $userId;
    public $clientId;
    public $createdAt;
    public $updatedAt;
    public $status;
    public $revenue;
    public $items;

    const ACCEPTABLE_STATUSES = ['created', 'payed', 'canceled'];

    public function __construct(
        string $orderId,
        string $userId,
        string $clientId,
        int $createdAt,
        int $updatedAt,
        string $status,
        int $revenue,
        iterable $items
    )
    {
        if (in_array($status, self::ACCEPTABLE_STATUSES)) {
            throw new \InvalidArgumentException(sprintf('Unknown status %s was given', $status));
        }

        $this->orderId = $orderId;
        $this->userId = $userId;
        $this->clientId = $clientId;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->status = $status;
        $this->revenue = $revenue;

        $this->items = (function (Item ...$items) {
            return $items;
        }) (...$items);
    }

    public function getAsArray(): array
    {
        $data = [
            'order_id' => $this->orderId,
            'user_id' => $this->userId,
            'client_id' => $this->clientId,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'status' => $this->status,
            'revenue' => $this->revenue,
        ];

        $items = [];
        array_map(
            function($item) use (&$items) {
                /** @var Item $item */
                $items[] = $item->getAsArray();
            },
            $this->items
        );

        $data['items'] = $items;

        return $data;
    }
}

