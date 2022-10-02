<?php

namespace App\Inventory\Domain\InventoryItems;

class Size
{
    protected float|int $quantity;
    protected string $measure;

    /**
     * @param float|int $quantity
     * @param string $measure
     */
    public function __construct(float|int $quantity, string $measure)
    {
        $this->quantity = $quantity;
        $this->measure = $measure;
    }

    public function quantity(): float|int
    {
        return $this->quantity;
    }

    public function measure(): string
    {
        return $this->measure;
    }

    public function __toString(): string
    {
        return $this->quantity . $this->measure;
    }
}
