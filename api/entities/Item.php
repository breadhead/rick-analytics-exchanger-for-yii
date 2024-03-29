<?php
namespace breadhead\rickAnalytics\api\entities;

class Item
{
    public $name;
    public $sku;
    public $price;
    public $quantity;
    public $category;

    public function __construct(
        string $name,
        string $sku,
        string $price,
        int $quantity,
        ?string $category = ''
    )
    {
        $this->name = $name;
        $this->sku = $sku;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->category = $category;
    }

    public function getAsArray(): array
    {
        return [
            'name' => $this->name,
            'sku' => $this->sku,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'category' => $this->category
        ];
    }
}
