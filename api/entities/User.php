<?php
namespace breadhead\rickAnalytics\api\entities;

class User
{
    public $userId;
    public $clientId;
    public $timestamp;

    public function __construct(string $userId, ?string $clientId, int $timestamp)
    {
        $this->userId = $userId;
        $this->clientId = $clientId;
        $this->timestamp = $timestamp;
    }
}
