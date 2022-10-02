<?php

namespace App\Inventory\Infrastructure\Persistence\Picqer\InventoryItems;

use Illuminate\Contracts\Support\Arrayable;

class PicqerWarehouse implements Arrayable
{
    protected int $idWarehouse;

    /**
     * @param int $idWarehouse
     */
    public function __construct(int $idWarehouse)
    {
        $this->idWarehouse = $idWarehouse;
    }


    public function toArray()
    {
        return [
            'idwarehouse' => $this->idWarehouse
        ];
    }
}
