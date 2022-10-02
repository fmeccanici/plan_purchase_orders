<?php

namespace App\Inventory\Domain\InventoryItems;

use App\SharedKernel\CleanArchitecture\ValueObject;
use Illuminate\Contracts\Support\Arrayable;

class Stock extends ValueObject implements Arrayable
{
    protected int $free;
    protected int $reserved;
    protected int $desired;
    protected int $toBeReceived;
    protected int $orderLevel;

    /**
     * @param int $free
     * @param int $reserved
     * @param int $desired
     * @param int $orderLevel
     * @param int $toBeReceived
     */
    public function __construct(int $free, int $reserved, int $desired, int $orderLevel = 0, int $toBeReceived = 0)
    {
        $this->free = $free;
        $this->reserved = $reserved;
        $this->desired = $desired;
        $this->toBeReceived = $toBeReceived;
        $this->orderLevel = $orderLevel;
    }

    public function free(): int
    {
        return $this->free;
    }

    public function reserved(): int
    {
        return $this->reserved;
    }

    public function desired(): int
    {
        return $this->desired;
    }

    public function current(): int
    {
        return $this->free - $this->reserved;
    }

    public function orderLevel(): int
    {
        return $this->orderLevel;
    }

    public function decrease($quantity): Stock
    {
        return new Stock($this->free - $quantity, $this->reserved, $this->orderLevel, $this->desired);
    }

    public function increase($quantity): Stock
    {
        return new Stock($this->free + $quantity, $this->reserved, $this->orderLevel, $this->desired);
    }

    public function toBeReceived(): int
    {
        return $this->toBeReceived;
    }

    public function needsReplenishment(): bool
    {
        return $this->free + $this->toBeReceived < $this->orderLevel;
    }

    public function toArray()
    {
        return [
            'current' => $this->current(),
            'desired' => $this->desired,
            'free' => $this->free,
            'reserved' => $this->reserved,
            'to_be_received' => $this->toBeReceived
        ];
    }
}
