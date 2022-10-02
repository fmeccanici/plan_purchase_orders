<?php


namespace App\Inventory\Infrastructure\Persistence\Picqer\Repositories;


use App\Inventory\Domain\Exceptions\InventoryItemRepositoryOperationException;
use App\Inventory\Domain\InventoryItems\InventoryItem;
use App\Inventory\Domain\InventoryItems\InventoryItemAnalytics;
use App\Inventory\Domain\InventoryItems\PurchaseSettings;
use App\Inventory\Domain\InventoryItems\Stock;
use App\Inventory\Domain\Repositories\DestinationInventoryItemRepositoryInterface;
use App\Inventory\Domain\Repositories\SourceInventoryItemRepositoryInterface;
use App\Inventory\Domain\Services\LabelGeneratorServiceInterface;
use App\Inventory\Infrastructure\ApiClients\PicqerApiClient;
use App\Inventory\Infrastructure\Exceptions\PicqerInventoryItemRepositoryException;
use App\Inventory\Infrastructure\Persistence\Picqer\InventoryItems\Mappers\PicqerInventoryItemMapper;
use App\Inventory\Infrastructure\Persistence\Picqer\InventoryItems\PicqerProduct;
use App\Inventory\Infrastructure\Persistence\Picqer\InventoryItems\PicqerStock;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Picqer\Api\Client;

class PicqerInventoryItemRepository implements SourceInventoryItemRepositoryInterface, DestinationInventoryItemRepositoryInterface
{
    public const GET_PURCHASE_ORDERS_CACHE_KEY = 'picqer_get_purchase_orders_response';

    private Client $apiClient;
    protected int $toBeReceived;

    public function __construct(PicqerApiClient $apiClient)
    {
        $this->apiClient = $apiClient->getClient();
    }

    /**
     * @inheritDoc
     */
    public function list(array $listOptions = []): LengthAwarePaginator
    {
        // TODO: Implement list() method.
    }

    /**
     * @inheritDoc
     * @throws InventoryItemRepositoryOperationException
     */
    public function save(InventoryItem $inventoryItem): InventoryItem
    {
        $apiResponse = $this->apiClient->getProductByProductcode($inventoryItem->productCode());

        // The API returns null or success = false if the product is not found, this differs that's why Arr:get solves both cases
        if (! Arr::get($apiResponse, 'success'))
        {
            // TODO: Get the correct values
            // Task 19109: Implementeer de correcte mappings in PicqerInventoryItemRepository
            $params = [
                'productcode' => $inventoryItem->productCode(),
                'name' => '',
                'price' => 0.0,
                'type' => $inventoryItem->type(),
                'minimum_purchase_quantity' => $inventoryItem->purchaseSettings()?->minimumPurchasingAmount(),
                'purchase_in_quantities_of' => $inventoryItem->purchaseSettings()?->purchaseInMultiplesOf()
            ];

            $apiResponse = $this->apiClient->addProduct($params);

            if (! Arr::get($apiResponse, 'success'))
            {
                throw new InventoryItemRepositoryOperationException('Failed adding inventory item, with error: ' . Arr::get($apiResponse, 'errormessage'));
            }

            $idProduct = Arr::get($apiResponse, 'data.idproduct');
            $params = [
                'idlocation' => null,
                'amount' => $inventoryItem->stock()->free(),
                'reason' => 'Adding a new product'
            ];

            $idWarehouse = $inventoryItem->warehouse()->identity();

            $apiResponse = $this->apiClient->updateProductStockForWarehouse($idProduct, $idWarehouse, $params);

            if (! Arr::get($apiResponse, 'success'))
            {
                throw new InventoryItemRepositoryOperationException('Failed updating stock for product with product code ' . $inventoryItem->productCode() . ' with error: ' . Arr::get($apiResponse, 'errormessage'));
            }

            $apiResponse = $this->apiClient->getProduct($idProduct);

            if (! Arr::get($apiResponse, 'success'))
            {
                throw new InventoryItemRepositoryOperationException('Failed getting product with idproduct ' . $idProduct . ', with error: ' . Arr::get($apiResponse, 'errormessage'));
            }
        }

        // TODO: Use mapper, Task 19143: Implementeer mapper in PicqerInventoryItemRepository
        $data = Arr::get($apiResponse, 'data');
        $productCode = Arr::get($data, 'productcode');
        $idProduct = Arr::get($data, 'idproduct');

        $stock = $this->getProductStock($productCode, $data);

        $minimumPurchasingAmount = $inventoryItem->purchaseSettings()?->minimumPurchasingAmount();
        $purchasingInMultiplesOf = $inventoryItem->purchaseSettings()?->purchaseInMultiplesOf();

        list($desiredStock, $orderLevel) = $this->getDesiredStockAndOrderLevelForProduct($idProduct);
        $productCodeSupplier = Arr::get($data, 'productcode_supplier');

        $picqerProduct = new PicqerProduct($idProduct, $productCode, $stock, $desiredStock, $orderLevel, collect(), $inventoryItem->type(), '', $purchasingInMultiplesOf, $minimumPurchasingAmount, $inventoryItem->supplierId(), $productCodeSupplier);

        return PicqerInventoryItemMapper::reconstituteEntityCore($picqerProduct);
    }

    /**
     * @inheritDoc
     */
    public function findOneByRegistrationNumber($registrationNumber): ?InventoryItem
    {
        // TODO: Implement find() method.
    }

    /**
     * @inheritDoc
     */
    public function findAllByRegistrationNumbers(array $registrationNumbers): Collection
    {
        // TODO: Implement getByIds() method.
    }

    /**
     * @inheritDoc
     */
    public function removeByRegistrationNumbers(array $registrationNumbers): void
    {
        // TODO: Implement removeByIds() method.
    }

    public function saveMultiple(Collection $inventoryItems): void
    {
        $inventoryItems->each(function (InventoryItem $inventoryItem) {
            $this->save($inventoryItem);
        });
    }

    /**
     * @throws PicqerInventoryItemRepositoryException
     */
    public function findAll(bool $withDesiredStock = true): Collection
    {
        $picqerProducts = $this->apiClient->getResultGenerator('product');
        $picqerProducts = collect($picqerProducts);

        $picqerProducts = $picqerProducts->filter(function (array $picqerProduct) {
            return Arr::get($picqerProduct, 'type') == 'virtual_composition' || Str::contains($picqerProduct['name'], 'Proefblik');
        });

        $picqerProducts = $picqerProducts->map(function (array $picqerProduct) use ($withDesiredStock) {
            $productCode = Arr::get($picqerProduct, 'productcode');
            $idProduct = Arr::get($picqerProduct, 'idproduct');
            $tags = collect(Arr::get($picqerProduct, 'tags'));
            $stock = collect(Arr::get($picqerProduct, 'stock'))->map(function (array $stock) {
                $idWarehouse = Arr::get($stock, 'idwarehouse');
                $freeStock = Arr::get($stock, 'freestock');
                $reservedStock = Arr::get($stock, 'reserved');
                $stock = Arr::get($stock, 'stock');
                return new PicqerStock($idWarehouse, $stock, $reservedStock, $freeStock);
            });

            $purchasingInMultiplesOf = Arr::get($picqerProduct, 'purchase_in_quantities_of');
            $minimumPurchasingAmount = Arr::get($picqerProduct, 'minimum_purchase_quantity');

            list($desiredStock, $orderLevel) = $withDesiredStock ? $this->getDesiredStockAndOrderLevelForProduct($idProduct) : 0;

            $supplierId = Arr::get($picqerProduct, 'idsupplier');
            $productCodeSupplier = Arr::get($picqerProduct, 'productcode_supplier');

            return new PicqerProduct($idProduct, $productCode, $stock, $desiredStock, $orderLevel, $tags, Arr::get($picqerProduct, 'type'), Arr::get($picqerProduct, 'name'), $purchasingInMultiplesOf, $minimumPurchasingAmount, $supplierId, $productCodeSupplier);
        });

        return PicqerInventoryItemMapper::reconstituteEntities($picqerProducts);
    }

    /**
     * @throws PicqerInventoryItemRepositoryException
     */
    public function update(InventoryItem $inventoryItem): void
    {
        $apiResponse = $this->apiClient->getProductByProductcode($inventoryItem->productCode());

        if (! Arr::get($apiResponse, 'success'))
        {
            throw new PicqerInventoryItemRepositoryException('Failed getting product with product code ' . $inventoryItem->productCode() . ', with error: ' . Arr::get($apiResponse, 'errormessage'));
        }

        $idProduct = Arr::get($apiResponse, 'data.idproduct');
        $tags = collect(Arr::get($apiResponse, 'tags'));

        list($desiredStock, $orderLevel) = $this->getDesiredStockAndOrderLevelForProduct($idProduct);

        if ($inventoryItem->warehouse() !== null)
        {
            $picqerStock = new PicqerStock($inventoryItem->warehouse()->identity(), $inventoryItem->stock()->current(), $inventoryItem->stock()->reserved(), $inventoryItem->stock()->free());
            $picqerProduct = new PicqerProduct($idProduct, $inventoryItem->productCode(), collect(array($picqerStock)), $desiredStock, $orderLevel, $tags, $inventoryItem->type(), '', $inventoryItem->purchaseSettings()->purchaseInMultiplesOf(), $inventoryItem->purchaseSettings()->minimumPurchasingAmount(), $inventoryItem->supplierId(), $inventoryItem->externalProductCode());

            $picqerProduct->stock()->each(function (PicqerStock $picqerStock) use ($idProduct) {
                $apiResponse = $this->apiClient->updateProductStockForWarehouse($idProduct, $picqerStock->idWarehouse(), [
                    'amount' => $picqerStock->freeStock(),
                    'reason' => 'Webservices Voorraad Synchronisatie'
                ]);

                if (! Arr::get($apiResponse, 'success'))
                {
                    throw new PicqerInventoryItemRepositoryException('Failed updating stock for idproduct ' . $idProduct . ', with error: ' . Arr::get($apiResponse, 'errormessage'));
                }
            });
        } else {
            $picqerProduct = new PicqerProduct($idProduct, $inventoryItem->productCode(), collect(), $desiredStock, $orderLevel, $tags, $inventoryItem->type(), '', $inventoryItem->purchaseSettings()->purchaseInMultiplesOf(), $inventoryItem->purchaseSettings()->minimumPurchasingAmount(), $inventoryItem->supplierId(), $inventoryItem->externalProductCode());
        }

        $inventoryItem->tags()->each(function (string $tag) use ($picqerProduct) {
                if (! $picqerProduct->tags()->contains($tag) )
                {
                    $apiResponse = $this->apiClient->getAllTags();

                    if (! Arr::get($apiResponse, 'success'))
                    {
                        throw new PicqerInventoryItemRepositoryException('Failed getting all tags');
                    }

                    $picqerTag = collect(Arr::get($apiResponse, 'data'))->filter(function (array $picqerTag) use ($tag) {
                        return Arr::get($picqerTag, 'title') == $tag;
                    })->first();

                    if ($picqerTag === null)
                    {
                        $apiResponse = $this->apiClient->createTag($tag);

                        if (! Arr::get($apiResponse, 'success'))
                        {
                            throw new PicqerInventoryItemRepositoryException('Failed creating tag with name ' . PicqerInventoryItemMapper::SYNC_STOCK_TAG);
                        }

                        $picqerTag = Arr::get($apiResponse, 'data');
                    }

                    $apiResponse = $this->apiClient->addProductTag($picqerProduct->idProduct(), Arr::get($picqerTag, 'idtag'));

                    if (! Arr::get($apiResponse, 'success'))
                    {
                        throw new PicqerInventoryItemRepositoryException('Failed adding product tag with idproduct ' . $picqerProduct->idProduct() . ' and tag ' . Arr::get($picqerTag, 'title'));
                    }
                }
        });
    }

    /**
     * @throws InventoryItemRepositoryOperationException
     */
    public function findOneByProductCode(string $productCode): ?InventoryItem
    {
        $apiResponse = $this->apiClient->getProductByProductcode($productCode);

        if (! Arr::get($apiResponse, 'success'))
        {
            $errorMessage = json_decode(Arr::get($apiResponse, 'errormessage'));
            $errorCode = $errorMessage->error_code;

            if ($errorCode == 20)
            {
                return null;
            }

            throw new InventoryItemRepositoryOperationException('Failed getting inventory item by product code, with error: ' . Arr::get($apiResponse, 'errormessage'));
        }

        $picqerInventoryItem = Arr::get($apiResponse, 'data');
        $idProduct = Arr::get($picqerInventoryItem, 'idproduct');
        $description = Arr::get($picqerInventoryItem, 'description');

        if (! $description)
        {
            $description = '';
        }

        // TODO: Add correct mappings
        // Task 19113: Gebruik de correcte mappings tussen Picqer en Domein
        $brand = '';
        $width = 0;
        $widthMeasure = 'cm';
        $length = 0;
        $lengthMeasure = 'cm';
        $height = 0;
        $heightMeasure = 'cm';
        $color = '';
        $location = '';

        $stock = Arr::get($picqerInventoryItem, 'stock');

        if (collect($stock)->isEmpty())
        {
            $freeStock = 0;
            $reservedStock = 0;
            $stock = 0;
        } else {
            $freeStock = Arr::get($stock[0], 'freestock');
            $reservedStock = Arr::get($stock[0], 'reserved');
            $stock = Arr::get($stock[0], 'stock');
        }

        $employeeWhoAddedInventoryItem = -1;
        $labelGenerator = App::make(LabelGeneratorServiceInterface::class);
        $createdAt = CarbonImmutable::create(2022, 01, 01);
        $type = '';
        $tags = collect(Arr::get($picqerInventoryItem, 'tags'))->map(function (array $tag) {
            return Arr::get($tag, 'title');
        });


        $supplierId = Arr::get($picqerInventoryItem, 'idsupplier');

        list($desiredStock, $orderLevel) = $this->getDesiredStockAndOrderLevelForProduct($idProduct);

        $stock = new Stock($freeStock, $reservedStock, $orderLevel, $desiredStock);
        $purchasingInMultiplesOf = Arr::get($picqerInventoryItem, 'purchase_in_quantities_of');
        $minimumPurchasingAmount = Arr::get($picqerInventoryItem, 'minimum_purchase_quantity');
        $purchaseSettings = new PurchaseSettings($purchasingInMultiplesOf, $minimumPurchasingAmount);

        $productCodeSupplier = Arr::get($picqerInventoryItem, 'productcode_supplier');

        return new InventoryItem($idProduct, $productCode, $description, $brand, $color, $width, $widthMeasure, $length, $lengthMeasure, $height, $heightMeasure, $location, $stock, $employeeWhoAddedInventoryItem, $labelGenerator, $createdAt, $type, $tags, $purchaseSettings, $supplierId, externalProductCode: $productCodeSupplier);
    }

    public function delete(InventoryItem $inventoryItem): void
    {
        // TODO: Implement delete() method.
    }

    /**
     * @throws PicqerInventoryItemRepositoryException
     */
    public function findAllByType(string $type): Collection
    {
        $apiResponse = $this->apiClient->getAllProducts();

        if (! Arr::get($apiResponse, 'success'))
        {
            throw new PicqerInventoryItemRepositoryException('Failed getting all products with type ' . $type);
        }

        $picqerProducts = collect(Arr::get($apiResponse, 'data'));

        $picqerProducts = $picqerProducts->filter(function (array $picqerProduct) use ($type) {
            return Arr::get($picqerProduct, 'type') == $type;
        });

        $picqerProducts = $picqerProducts->map(function (array $picqerProduct) use ($type) {
            $productCode = Arr::get($picqerProduct, 'productcode');
            $idProduct = Arr::get($picqerProduct, 'idproduct');
            $tags = collect(Arr::get($picqerProduct, 'tags'));
            $stock = collect(Arr::get($picqerProduct, 'stock'))->map(function (array $stock) {
                $idWarehouse = Arr::get($stock, 'idwarehouse');
                $freeStock = Arr::get($stock, 'freestock');
                $reservedStock = Arr::get($stock, 'reserved');

                $stock = Arr::get($stock, 'stock');
                return new PicqerStock($idWarehouse, $stock, $reservedStock, $freeStock);
            });
            $name = Arr::get($picqerProduct, 'name');
            $minimumPurchaseQuantity = Arr::get($picqerProduct, 'minimum_purchase_quantity');
            $purchaseInQuantitiesOf = Arr::get($picqerProduct, 'purchase_in_quantities_of');
            $idSupplier = Arr::get($picqerProduct, 'idsupplier');

            list($desiredStock, $orderLevel) = $this->getDesiredStockAndOrderLevelForProduct($idProduct);

            $productCodeSupplier = Arr::get($picqerProduct, 'productcode_supplier');

            return new PicqerProduct($idProduct, $productCode, $stock, $desiredStock, $orderLevel, $tags, $type, $name, $purchaseInQuantitiesOf, $minimumPurchaseQuantity, $idSupplier, $productCodeSupplier);
        });

        return PicqerInventoryItemMapper::reconstituteEntities($picqerProducts);
    }

    public function findAllByProductCode(string $productCode): Collection
    {
        return collect([$this->findOneByProductCode($productCode)]);
    }

    public function getAnalytics(CarbonImmutable $startRangeDate = null, CarbonImmutable $endRangeDate = null, string $type = null): ?InventoryItemAnalytics
    {
        // TODO: Implement getStatistics() method.
    }

    /**
     * @throws PicqerInventoryItemRepositoryException
     */
    public function findAllByTagAndSupplierId(string $tag, int $supplierId): Collection
    {
        $apiResponse = $this->apiClient->getAllProducts([
            'tag' => $tag,
            'idsupplier' => $supplierId
        ]);

        if (! Arr::get($apiResponse, 'success'))
        {
            throw new PicqerInventoryItemRepositoryException('Failed getting all products with tag ' . $tag);
        }

        $picqerProducts = collect(Arr::get($apiResponse, 'data'));
        $picqerProducts = $picqerProducts->map(function (array $picqerProduct) {
            $productCode = Arr::get($picqerProduct, 'productcode');
            $idProduct = Arr::get($picqerProduct, 'idproduct');
            $tags = collect(Arr::get($picqerProduct, 'tags'));
            $stock = $this->getProductStock($productCode, $picqerProduct);

            $name = Arr::get($picqerProduct, 'name');
            $minimumPurchaseQuantity = Arr::get($picqerProduct, 'minimum_purchase_quantity');
            $purchaseInQuantitiesOf = Arr::get($picqerProduct, 'purchase_in_quantities_of');
            $type = Arr::get($picqerProduct, 'type');
            $supplierId = Arr::get($picqerProduct, 'idsupplier');

            list($desiredStock, $orderLevel) = $this->getDesiredStockAndOrderLevelForProduct($idProduct);

            $productCodeSupplier = Arr::get($picqerProduct, 'productcode_supplier');


            return new PicqerProduct($idProduct, $productCode, $stock, $desiredStock, $orderLevel, $tags, $type, $name, $purchaseInQuantitiesOf, $minimumPurchaseQuantity, $supplierId, productCodeSupplier: $productCodeSupplier);
        });

        return PicqerInventoryItemMapper::reconstituteEntities($picqerProducts);
    }

    /**
     * @param mixed $idProduct
     * @return array|\ArrayAccess|int|mixed
     * @throws InventoryItemRepositoryOperationException
     */
    protected function getDesiredStockAndOrderLevelForProduct(int $idProduct): mixed
    {
        $getProductWarehouseSettingsApiResponse = $this->apiClient->getProductWarehouseSettings($idProduct);

        if (! Arr::get($getProductWarehouseSettingsApiResponse, 'success')) {
            throw new InventoryItemRepositoryOperationException('Failed getting warehouse settings for product with id ' . $idProduct . ', with error: ' . Arr::get($getProductWarehouseSettingsApiResponse, 'errormessage'));
        }

        $productWarehouseSettings = Arr::get($getProductWarehouseSettingsApiResponse, 'data');

        if (collect($productWarehouseSettings)->isEmpty()) {
            $desiredStock = 0;
            $orderLevel = 0;
        } else {
            $desiredStock = Arr::get($productWarehouseSettings[0], 'stock_level_desired', 0);
            $orderLevel = Arr::get($productWarehouseSettings[0], 'stock_level_order', 0);
        }

        return array($desiredStock, $orderLevel);
    }

    /**
     * @param mixed $productCode
     * @param array $picqerProduct
     * @return Collection
     * @throws InventoryItemRepositoryOperationException
     */
    protected function getProductStock(mixed $productCode, array $picqerProduct): Collection
    {
        // Only get purchase order with status purchased, because Picqer does not calculate 'Te ontvangen' with other purchase orders
        $cacheKey = self::GET_PURCHASE_ORDERS_CACHE_KEY;
        if (Cache::has($cacheKey))
        {
            $getPurchaseOrdersResponse = Cache::get($cacheKey);
        } else {
            $getPurchaseOrdersResponse = $this->apiClient->getPurchaseorders([
                'status' => 'purchased'
            ]);

            Cache::put($cacheKey, $getPurchaseOrdersResponse);
        }

        if (!Arr::get($getPurchaseOrdersResponse, 'success')) {
            throw new InventoryItemRepositoryOperationException('Failed purchase orders, with error: ' . Arr::get($getPurchaseOrdersResponse, 'errormessage'));
        }

        $toBeReceived = 0;
        $picqerPurchaseOrders = collect(Arr::get($getPurchaseOrdersResponse, 'data'));

        $picqerPurchaseOrders->each(function (array $picqerPurchaseOrder) use ($productCode, &$toBeReceived) {
            $picqerProductsOnPurchaseOrder = collect(Arr::get($picqerPurchaseOrder, 'products'));

            $picqerProductOnPurchaseOrder = $picqerProductsOnPurchaseOrder->first(function (array $picqerProductOnPurchaseOrder) use ($productCode) {
                return Arr::get($picqerProductOnPurchaseOrder, 'productcode') === $productCode;
            });

            if ($picqerProductOnPurchaseOrder !== null) {
                $amount = Arr::get($picqerProductOnPurchaseOrder, 'amount');
                $amountReceived = Arr::get($picqerProductOnPurchaseOrder, 'amountreceived');
                $toBeReceived += ($amount - $amountReceived);
            }
        });

        $picqerStock = collect(Arr::get($picqerProduct, 'stock'));

        // Needed because when the stock is empty, Picqer will return an empty array here
        if ($picqerStock->isEmpty())
        {
            return collect([new PicqerStock(config('warehouse.id_warehouse'), 0, 0, 0, $toBeReceived)]);
        }

        return collect($picqerStock)->map(function (array $stock) use ($toBeReceived) {
            $idWarehouse = Arr::get($stock, 'idwarehouse');
            $freeStock = Arr::get($stock, 'freestock');
            $reservedStock = Arr::get($stock, 'reserved');
            $stock = Arr::get($stock, 'stock');

            return new PicqerStock($idWarehouse, $stock, $reservedStock, $freeStock, $toBeReceived);
        });
    }

    public function findAllAvailableByProductCode(string $productCode): Collection
    {
        // TODO: Implement findAllAvailableByProductCode() method.
    }
}
