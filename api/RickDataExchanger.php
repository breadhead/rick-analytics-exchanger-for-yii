<?php
namespace breadhead\rickAnalytics\api;

use breadhead\rickAnalytics\api\entities\Deal;
use breadhead\rickAnalytics\api\entities\Item;
use breadhead\rickAnalytics\api\entities\User;

class RickDataExchanger
{
    /** @var RickApi  */
    private $rickApi;
    private $serverInfo = [];

    public function __construct(RickApi $rickApi, array $serverInfo)
    {
        $this->rickApi = $rickApi;
        $this->serverInfo = $serverInfo;
    }

    public function createDeal(Deal $deal): void
    {
        $fields = $this->makeFieldsFromDeal($deal);

        $this->rickApi->sendTransaction('create', $fields);
    }

    public function updateDeal(Deal $deal): void
    {
        $fields = $this->makeFieldsFromDeal($deal);

        $this->rickApi->sendTransaction('update', $fields);
    }

    public function checkDeals(iterable $deals): void
    {
        $deals = (function (Deal ...$deals) {
            return $deals;
        }) (...$deals);

        $fields = [];
        array_map(
            function($deal) use (&$fields) {
                $fields[] = $this->makeFieldsFromDeal($deal);
            },
            $deals
        );

        $this->rickApi->sendTransaction('check', $fields);
    }

    public function createUser(User $user): void
    {
        $fields = [
            'user_id' => $this->getPlatformPrefix() . $user->userId,
            'client_id' => $user->clientId,
            'new_lead' => true,
            'lead_created_at' => $user->timestamp
        ];

        $this->rickApi->sendLead($fields);
    }

    private function makeFieldsFromDeal(Deal $deal): array
    {
        $items = [];
        array_map(
            function($item) use (&$items) {
                $items[] = $this->makeFieldsFromItem($item);
            },
            $deal->items
        );

        return [
            'transaction_id' => $this->getPlatformPrefix() . $deal->orderId,
            'user_id' => $this->getPlatformPrefix() . $deal->userId,
            'client_id' => $deal->clientId,
            'deal_created_at' => $deal->createdAt,
            'deal_updated_at' => $deal->updatedAt,
            'status' => $deal->status,
            'revenue' => $deal->revenue,
            'items' => $items,
            'currency' => 'USD'
        ];
    }

    private function makeFieldsFromItem(Item $item): array
    {
        return [
            "name" => $item->name,
            "sku" => $item->sku,
            "price" => $item->price,
            "quantity" => $item->quantity,
            "category" => $item->category
        ];
    }

    private function getPlatformPrefix(): string
    {
        return $this->serverInfo['platform_prefix'];
    }
}
