<?php


namespace App\Inventory\Infrastructure\Persistence\Picqer\InventoryItems\Mappers;


use App\Inventory\Domain\InventoryItems\InventoryItem;
use App\Inventory\Domain\InventoryItems\PurchaseSettings;
use App\Inventory\Domain\InventoryItems\Stock;
use App\Inventory\Domain\InventoryItems\Warehouse;
use App\Inventory\Infrastructure\Persistence\EntityMapperTrait;
use App\Inventory\Infrastructure\Persistence\ModelMapperTrait;
use App\Inventory\Infrastructure\Persistence\Picqer\InventoryItems\PicqerProduct;
use App\Inventory\Infrastructure\Persistence\Picqer\InventoryItems\PicqerStock;
use App\Inventory\Infrastructure\Persistence\ReflectionClassCache;
use Illuminate\Support\Arr;

class PicqerInventoryItemMapper extends InventoryItem
{
    public const SYNC_STOCK_TAG = 'Synchroniseer Voorraad';

    use EntityMapperTrait;
    use ModelMapperTrait;

    public static function reconstituteEntityCore(PicqerProduct $picqerProduct)
    {
        $productClass = ReflectionClassCache::getReflectionClass(InventoryItem::class);
        /** @var InventoryItem $entity */
        $entity = $productClass->newInstanceWithoutConstructor();

        $entity->id = $picqerProduct->idProduct();
        $entity->productCode = $picqerProduct->productCode();
        $entity->externalProductCode = $picqerProduct->productCodeSupplier();

        $freeStock = $picqerProduct->stock()->first()?->freeStock() ?? 0;
        $reservedStock = $picqerProduct->stock()->first()?->reservedStock() ?? 0;
        $desiredStock = $picqerProduct->desiredStock();
        $toBeReceivedStock = $picqerProduct->stock()->first()?->toBeReceivedStock() ?? 0;
        $orderLevel = $picqerProduct->orderLevel();

        $entity->stock = new Stock($freeStock, $reservedStock, $desiredStock, $orderLevel, $toBeReceivedStock);
        $entity->tags = collect($picqerProduct->tags())->map(function (array $tag) {
            return Arr::get($tag, 'title');
        });

        $entity->type = $picqerProduct->type();

        if ($picqerProduct->stock()->first() === null)
        {
            $entity->warehouse = null;
        } else {
            $entity->warehouse = new Warehouse();
            $entity->warehouse->setIdentity($picqerProduct->stock()->first()->idWarehouse());
        }

        $entity->purchaseSettings = new PurchaseSettings($picqerProduct->purchaseInQuantitiesOf(), $picqerProduct->minimumPurchaseQuantity());
        $entity->supplierId = $picqerProduct->idSupplier();

        // mapToEntity hasOne's

        // mapToEntities hasMany's

        return $entity;
    }

    protected static function createModelCore(InventoryItem $entity): void
    {
        $stock = new PicqerStock($entity->warehouse->identity(), $entity->stock->current(), $entity->stock()->reserved(0), $entity->stock()->free());
        $model = new PicqerProduct($entity->identity(), $entity->productCode(), $stock, $entity->stock()->desired(), $entity->stock()->orderLevel(), collect(), $entity->type, $entity->productCode, $entity->purchaseSettings()->purchaseInMultiplesOf(), $entity->purchaseSettings()->minimumPurchasingAmount(), $entity->supplierId, $entity->externalProductCode);

        $entity->setIdentity($model->idProduct());

        // mapToModel hasOne's

        // mapToModels hasMany's
    }

}
