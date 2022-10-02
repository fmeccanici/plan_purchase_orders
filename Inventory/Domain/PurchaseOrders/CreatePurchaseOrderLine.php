<?php

namespace App\Inventory\Domain\PurchaseOrders;

use Faker\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class CreatePurchaseOrderLine
{
    public static function multiple(int $quantity, array $attributes = []): Collection
    {
        $faker = Factory::create();
        $result = collect();

        for ($i = 0; $i < $quantity; $i ++)
        {
            $productCode = Arr::get($attributes, 'product_code', uniqid());
            $productCodeSupplier = Arr::get($attributes, 'product_code_supplier', uniqid());
            $productName = Arr::get($attributes, 'product_name', $faker->name);
            $quantity = rand(1, 10);
            $price = $faker->randomFloat();
            $deliveryWorkDays = random_int(1, 7);

            $result->push( new PurchaseOrderLine($productCode, $productCodeSupplier, $productName, $quantity, $price, $deliveryWorkDays) );
        }

        return $result;
    }
}
