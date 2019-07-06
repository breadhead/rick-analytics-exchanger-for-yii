<?php
namespace breadhead\rickAnalytics\eventLogger;

class Event
{
    const TYPE_CREATE = 'create';
    const TYPE_UPDATE = 'update';
    const TYPE_REGISTER = 'lead';
    const TYPE_CHECK = 'check';

    private $id;
    private $eventType;
    private $data;
    private $clientId;
    private $dealId;
    private $status;
    private $createdAt;
    private $updatedAt;

    public function __construct(
        ?string $id,
        string $eventType,
        array $data,
        string $clientId,
        string $dealId,
        int $status,
        ?int $createdAt,
        ?int $updatedAt
    ) {
        $this->id = $id;
        $this->eventType = $eventType;
        $this->data = $data;
        $this->clientId = $clientId;
        $this->dealId = $dealId;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getDealId(): string
    {
        return $this->dealId;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function changeStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getCreatedAt(): ?int
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?int
    {
        return $this->updatedAt;
    }

    public function changeUpdatedAt(int $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
