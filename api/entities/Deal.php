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
}

