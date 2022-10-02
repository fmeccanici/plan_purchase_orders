<?php

namespace App\Inventory\Domain\PurchaseRecommendations;

use App\Inventory\Domain\InventoryItems\PurchaseSettings;
use App\Inventory\Domain\InventoryItems\Stock;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class CreatePurchaseOrderRecommendation
{
    public static function multiple(int $quantity, array $attributes = []): Collection
    {
        $result = collect();

        for ($i = 0; $i < $quantity; $i++)
        {
            $stock = Arr::get($attributes, 'stock', new Stock(random_int(1, 99), random_int(1, 99), random_int(1, 99)));
            $purchaseSettings = Arr::get($attributes, 'purchase_settings', new PurchaseSettings());
            $productCode = Arr::get($attributes, 'product_code', uniqid());
            $productCodeSupplier = Arr::get($attributes, 'product_code_supplier', uniqid());
            $supplierId = Arr::get($attributes, 'supplier_id', 1);
            $tag = Arr::get($attributes, 'tag', 'Onderhoudsproducten');
            $purchaseRecommendation = new PurchaseRecommendation($stock, $purchaseSettings, $productCode, $productCodeSupplier, $supplierId, $tag);
            $result->push($purchaseRecommendation);
        }

        return $result;
    }
}
