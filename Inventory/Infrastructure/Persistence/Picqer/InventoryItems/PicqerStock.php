<?php

namespace App\Inventory\Infrastructure\Persistence\Picqer\InventoryItems;

use Illuminate\Contracts\Support\Arrayable;

class PicqerStock implements Arrayable
{
    protected int $idWarehouse;
    protected int $stock;
    protected int $freeStock;
    protected int $reservedStock;
    protected int $toBeReceivedStock;

    /**
     * @param int|null $idWarehouse
     * @param int $stock
     * @param int $reservedStock
     * @param int $freeStock
     * @param int $toBeReceivedStock
     */
    public function __construct(?int $idWarehouse, int $stock, int $reservedStock, int $freeStock, int $toBeReceivedStock = 0)
    {
        $this->idWarehouse = $idWarehouse;
        $this->stock = $stock;
        $this->reservedStock = $reservedStock;
        $this->freeStock = $freeStock;
        $this->toBeReceivedStock = $toBeReceivedStock;
    }

    public function idWarehouse(): ?int
    {
        return $this->idWarehouse;
    }

    public function stock(): int
    {
        return $this->freeStock - $this->reservedStock;
    }

    public function freeStock(): int
    {
        return $this->freeStock;
    }

    public function reservedStock(): int
    {
        return $this->reservedStock;
    }

    public function toBeReceivedStock(): int
    {
        return $this->toBeReceivedStock;
    }

    public function changeStock(int $stock)
    {
        $this->freeStock = $stock;
    }

    public function increaseStock(int $stock)
    {
        $this->freeStock += $stock;
    }

    public function toArray()
    {
        return [
            'idwarehouse' => $this->idWarehouse,
            'stock' => $this->stock,
            'freestock' => $this->freeStock,
            'reserved' => $this->reservedStock
        ];
    }
}
