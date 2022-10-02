<?php

namespace App\Inventory\Domain\InventoryItems;

use App\Inventory\Domain\Services\LabelGeneratorServiceInterface;
use Carbon\CarbonImmutable;
use Faker\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

class InventoryItemFactory
{
    public static function create(array $attributes = [])
    {
        $faker = Factory::create();

        $registrationNumber = Arr::get($attributes, 'registrationNumber');

        if ($registrationNumber === null)
        {
            $registrationNumber = uniqid();
        }

        $productCode = Arr::get($attributes, 'productCode');

        if ($productCode === null)
        {
            $productCode = uniqid();
        }

        $description = Arr::get($attributes, 'description');

        if ($description === null)
        {
            $description = $faker->text;
        }

        $brand = Arr::get($attributes, 'brand');

        if ($brand === null)
        {
            $brand = $faker->text;
        }

        $color = Arr::get($attributes, 'color');

        if ($color === null)
        {
            $color = $faker->text;
        }

        $width = Arr::get($attributes, 'width');

        if ($width === null)
        {
            $width = rand(1, 100);
        }

        $widthMeasure = Arr::get($attributes, 'widthMeasure');

        if ($widthMeasure === null)
        {
            $widthMeasure = "cm";
        }

        $length = Arr::get($attributes, 'length');

        if ($length === null)
        {
            $length = rand(1, 100);
        }

        $lengthMeasure = Arr::get($attributes, 'lengthMeasure');

        if ($lengthMeasure === null)
        {
            $lengthMeasure = "cm";
        }

        $height = Arr::get($attributes, 'height');

        if ($height === null)
        {
            $height = rand(1, 100);
        }

        $heightMeasure = Arr::get($attributes, 'heightMeasure');

        if ($heightMeasure === null)
        {
            $heightMeasure = "cm";
        }

        $location = Arr::get($attributes, 'location');

        if ($location === null)
        {
            $location = (string) rand(1, 999);
        }

        $stock = Arr::get($attributes, 'stock', new Stock(rand(1, 99), rand(1, 99), rand(1, 99), rand(1, 99)));
        $supplierId = Arr::get($attributes, 'supplierId');
        $purchaseSettings = Arr::get($attributes, 'purchaseSettings', new PurchaseSettings());
        $employeeWhoAddedInventoryItem = Arr::get($attributes, 'employeeWhoAddedInventoryItem');

        if ($employeeWhoAddedInventoryItem === null)
        {
            $employeeWhoAddedInventoryItem = rand(1, 10);
        }

        $soldPrice = Arr::get($attributes, 'soldPrice');

        if ($soldPrice === null)
        {
            $soldPrice = $faker->randomFloat(2);
        }

        $labelGeneratorService = Arr::get($attributes, 'labelGeneratorService');

        if ($labelGeneratorService === null)
        {
            $labelGeneratorService = App::make(LabelGeneratorServiceInterface::class);
        }

        $soldAt = Arr::get($attributes, 'soldAt');

        if ($soldAt === null)
        {
            $soldAt = CarbonImmutable::parse($faker->date());
        }

        $createdAt = Arr::get($attributes, 'createdAt');

        if ($createdAt === null)
        {
            $createdAt = CarbonImmutable::now();
        }

        $type = Arr::get($attributes, 'type');

        if ($type === null)
        {
            $types = InventoryItem::TYPES;
            $index = array_rand(InventoryItem::TYPES);
            $type = $types[$index];
        }

        $warehouse = Arr::get($attributes, 'warehouse');

        if (! $warehouse)
        {
            $warehouse = new Warehouse();
            $warehouse->setIdentity(uniqid());
        }

        $tags = Arr::get($attributes, 'tags');

        if (! $tags)
        {
            $tags = collect();
        }


        $productCodeSupplier = Arr::get($attributes, 'productCodeSupplier', uniqid());


        return new InventoryItem($registrationNumber, $productCode, $description, $brand, $color, $width, $widthMeasure, $length, $lengthMeasure, $height, $heightMeasure, $location, $stock, $employeeWhoAddedInventoryItem, $labelGeneratorService, $createdAt, $type, $tags, $purchaseSettings, $supplierId, $warehouse, $soldPrice, $soldAt, externalProductCode: $productCodeSupplier);
    }

    // TODO: Reuse create function, need to find a way to pass null for sold_at and sold_price
    public static function createNotSold(array $attributes = []): InventoryItem
    {
        $faker = Factory::create();

        $registrationNumber = Arr::get($attributes, 'registrationNumber');

        if ($registrationNumber === null)
        {
            $registrationNumber = uniqid();
        }

        $productCode = Arr::get($attributes, 'productCode');

        if ($productCode === null)
        {
            $productCode = uniqid();
        }

        $description = Arr::get($attributes, 'description');

        if ($description === null)
        {
            $description = $faker->text;
        }

        $brand = Arr::get($attributes, 'brand');

        if ($brand === null)
        {
            $brand = $faker->text;
        }

        $color = Arr::get($attributes, 'color');

        if ($color === null)
        {
            $color = $faker->text;
        }

        $width = Arr::get($attributes, 'width');

        if ($width === null)
        {
            $width = rand(1, 100);
        }

        $widthMeasure = Arr::get($attributes, 'widthMeasure');

        if ($widthMeasure === null)
        {
            $widthMeasure = "cm";
        }

        $length = Arr::get($attributes, 'length');

        if ($length === null)
        {
            $length = rand(1, 100);
        }

        $lengthMeasure = Arr::get($attributes, 'lengthMeasure');

        if ($lengthMeasure === null)
        {
            $lengthMeasure = "cm";
        }

        $height = Arr::get($attributes, 'height');

        if ($height === null)
        {
            $height = rand(1, 100);
        }

        $heightMeasure = Arr::get($attributes, 'heightMeasure');

        if ($heightMeasure === null)
        {
            $heightMeasure = "cm";
        }

        $location = Arr::get($attributes, 'location');

        if ($location === null)
        {
            $location = (string) rand(1, 999);
        }

        $stock = Arr::get($attributes, 'stock', new Stock(rand(1, 99), rand(1, 99), rand(1, 99)));
        $supplierId = Arr::get($attributes, 'supplierId');
        $purchaseSettings = Arr::get($attributes, 'purchaseSettings', new PurchaseSettings());

        $employeeWhoAddedInventoryItem = Arr::get($attributes, 'employeeWhoAddedInventoryItem');

        if ($employeeWhoAddedInventoryItem === null)
        {
            $employeeWhoAddedInventoryItem = rand(1, 10);
        }

        $labelGeneratorService = Arr::get($attributes, 'labelGeneratorService');

        if ($labelGeneratorService === null)
        {
            $labelGeneratorService = App::make(LabelGeneratorServiceInterface::class);
        }

        $createdAt = Arr::get($attributes, 'createdAt');

        if ($createdAt === null)
        {
            $createdAt = CarbonImmutable::now();
        }

        $type = Arr::get($attributes, 'type');

        if ($type === null)
        {
            $types = InventoryItem::TYPES;
            $index = array_rand(InventoryItem::TYPES);
            $type = $types[$index];
        }

        $warehouse = Arr::get($attributes, 'warehouse');

        if (! $warehouse)
        {
            $warehouse = new Warehouse();
            $warehouse->setIdentity(uniqid());
        }

        $tags = Arr::get($attributes, 'tags');

        if (! $tags)
        {
            $tags = collect();
        }

        return new InventoryItem($registrationNumber, $productCode, $description, $brand, $color, $width, $widthMeasure, $length, $lengthMeasure, $height, $heightMeasure, $location, $stock, $employeeWhoAddedInventoryItem, $labelGeneratorService, $createdAt, $type, $tags, $purchaseSettings, $supplierId, $warehouse);
    }

    public static function createMultiple(int $_quantity, array $attributes = []): Collection
    {
        $result = collect();
        for ($i = 0; $i < $_quantity; $i++)
        {
            $inventoryItem = self::create($attributes);
            $result->push($inventoryItem);
        }

        // Needed for testing Eloquent Inventory Item Repository which generates registration number from database.
        // This ensures that we can do assertEquals(), as the id's match
        $result->map(function (InventoryItem $inventoryItem, $key) {
            $registrationNumber = (string) ($key + 1);
            $inventoryItem->changeRegistrationNumber($registrationNumber);
        });

        return $result;
    }

    public static function createMultipleNotSold(int $_quantity, array $attributes): Collection
    {
        $result = collect();
        for ($i = 0; $i < $_quantity; $i++)
        {
            $inventoryItem = self::createNotSold($attributes);
            $result->push($inventoryItem);
        }

        return $result;
    }
}
