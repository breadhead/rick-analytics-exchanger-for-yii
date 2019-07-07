<?php
namespace breadhead\rickAnalytics\eventLogger;

class Event
{
    const TYPE_CREATE = 'create';
    const TYPE_UPDATE = 'update';
    const TYPE_REGISTER = 'lead';
    const TYPE_CHECK = 'check';

    const STATUS_NEW = 10;
    const STATUS_IN_PROGRESS = 5;
    const STATUS_DONE = 0;
    const STATUS_FAIL = 7;

    private $id;
    private $eventType;
    private $data;
    private $clientId;
    private $dealId;
    private $status;
    private $createdAt;
    private $updatedAt;
    private $error;

    public function __construct(
        ?string $id,
        string $eventType,
        array $data,
        string $clientId,
        int $status,
        ?string $dealId,
        ?int $createdAt = null,
        ?int $updatedAt = null,
        ?string $error
    ) {
        $this->id = $id;
        $this->eventType = $eventType;
        $this->data = $data;
        $this->clientId = $clientId;
        $this->dealId = $dealId;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->error = $error;
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

    public function getDealId(): ?string
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

    public function getError(): ?string
    {
        return $this->error;
    }

    public function changeError(string $error): void
    {
        $this->error = $error;
    }
}
