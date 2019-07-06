<?php
namespace breadhead\rickAnalytics\eventLogger;

interface EventRepositoryInterface
{
    public function update(Event $event): void;

    public function insert(Event $event): void;

    public function findOne(array $filter): ?Event;

    public function find(array $filter): ?array;
}
