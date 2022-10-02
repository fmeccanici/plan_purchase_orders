<?php

namespace App\Inventory\Infrastructure\Persistence\Picqer\PurchaseOrders\Mappers;

use App\Inventory\Domain\PurchaseOrders\PurchaseOrderStatus;
use Illuminate\Support\Arr;

class PurchaseOrderStateMapper
{
    public static function toEntity(string $state)
    {
        $mapping = [
            'concept' => PurchaseOrderStatus::CONCEPT,
            'purchased' => PurchaseOrderStatus::PURCHASED
        ];

        $mappedState = Arr::get($mapping, $state, 'unkown');

        return new PurchaseOrderStatus($mappedState);
    }
}
