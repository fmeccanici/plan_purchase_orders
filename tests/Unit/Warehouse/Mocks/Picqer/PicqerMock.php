<?php

namespace Tests\Unit\Warehouse\Mocks\Picqer;

use App\Inventory\Infrastructure\Persistence\Picqer\InventoryItems\PicqerProduct;
use App\Inventory\Infrastructure\Persistence\Picqer\InventoryItems\PicqerStock;
use App\Inventory\Infrastructure\Persistence\Picqer\InventoryItems\PicqerWarehouse;
use App\Inventory\Infrastructure\Persistence\Picqer\Suppliers\PicqerSupplier;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Picqer\Api\Client;

class PicqerMock extends Client
{
    private \Illuminate\Support\Collection $products;
    private int $idProductCount;
    private int $idWarehouseCount;
    private \Illuminate\Support\Collection $warehouses;
    private \Illuminate\Support\Collection $batchPicklists;
    private Collection $tags;
    private int $idTagCount;
    private Collection $users;
    private Collection $suppliers;
    private int $idSupplierCount;

    public function __construct()
    {
        $this->products = collect();
        $this->warehouses = collect();
        $this->batchPicklists = collect();
        $this->tags = collect();
        $this->users = collect();
        $this->suppliers = collect();
        $this->idProductCount = 0;
        $this->idWarehouseCount = 0;
        $this->idTagCount = 0;
        $this->idSupplierCount = 0;
    }

    public function getProductByProductcode($productcode)
    {
        $product = $this->products->first(function (PicqerProduct $picqerProduct) use ($productcode) {
            return $picqerProduct->productCode() === $productcode;
        });

        if ($product)
        {
            return [
                'success' => true,
                'data' => $product->toArray()
            ];
        } else {
            return [
                'success' => false,
                'errormessage' => 'Product with product code '  . $productcode . ' not found'
            ];
        }
    }

    public function addProduct($params)
    {
        $idProduct = $this->idProductCount;
        $this->idProductCount ++;

        // TODO: Correctly map and do not use -1
        $picqerProduct = new PicqerProduct($idProduct, Arr::get($params, 'productcode'), collect(), -1, -1, collect(), Arr::get($params, 'type'), Arr::get($params, 'name'), Arr::get($params, 'purchase_in_quantities_of'), Arr::get($params, 'minimum_purchase_quantity'), Arr::get($params, 'idsupplier'), -1);

        $this->products->add($picqerProduct);

        return [
            'success' => true,
            'data' => $picqerProduct->toArray()
        ];
    }

    public function updateProductStockForWarehouse($idproduct, $idwarehouse, $params)
    {
        $product = $this->products->first(function (PicqerProduct $picqerProduct) use ($idproduct) {
            return $idproduct === $picqerProduct->idProduct();
        });

        if (! $product)
        {
            return [
                'success' => false,
                'errormessage' => 'Product with idproduct ' . $idproduct . ' not found'
            ];
        }

        $stock = $product->stock()->filter(function (PicqerStock $picqerStock) use ($idwarehouse, $params) {
            if ($picqerStock->idWarehouse() == $idwarehouse)
            {
                $amount = Arr::get($params, 'amount');
                $change = Arr::get($params, 'change');

                if ($amount)
                {
                    $picqerStock->changeStock($amount);
                } else if ($change)
                {
                    $picqerStock->increaseStock($change);
                }
            }
        });

        if ($stock->isEmpty())
        {
            $stock = new PicqerStock((int) $idwarehouse, Arr::get($params, 'amount'), 0, Arr::get($params, 'amount'));

            $product->addStock($stock);
        }

        return [
            'success' => true,
            'data' => $product->toArray()
        ];
    }

    public function addWarehouse()
    {
        $idWarehouse = $this->idWarehouseCount;
        $this->idWarehouseCount ++;
        $warehouse = new PicqerWarehouse($idWarehouse);
        $this->warehouses->add($warehouse);

        return [
            'success' => true,
            'data' => $warehouse->toArray()
        ];
    }

    public function getProduct($idproduct)
    {
        $product = $this->products->first(function (PicqerProduct $picqerProduct) use ($idproduct) {
            return $picqerProduct->idProduct() == $idproduct;
        });

        return [
            'success' => true,
            'data' => $product->toArray()
        ];
    }

    public function getProducts($filters = [])
    {
        return [
            'data' => $this->products->toArray(),
            'success' => true
        ];
    }

    public function getAllBatchPicklists($filters = [])
    {
        return [
            'data' => $this->batchPicklists->toArray(),
            'success' => true
        ];
    }

    public function createTag(string $title, string $color = '#823882', bool $inherit = false)
    {
        $tag = [
            'idtag' => $this->idTagCount,
            'title' => $title,
            'color' => $color,
            'inherit' => $inherit
        ];

        $this->tags->push($tag);
        $this->idTagCount ++;

        return [
            'data' => $tag,
            'success' => true
        ];
    }

    public function getAllTags($filters = [])
    {
        return [
            'data' => $this->tags->toArray(),
            'success' => true
        ];
    }

    public function addProductTag($idproduct, $idtag)
    {
        $tag = $this->tags->first(function (array $tag) use ($idtag) {
            return Arr::get($tag, 'idtag') == $idtag;
        });

        $this->products->transform(function (PicqerProduct $picqerProduct) use ($idproduct, $tag) {
            if ($picqerProduct->idProduct() == $idproduct)
            {
                $picqerProduct->addTag($tag);
            }

            return $picqerProduct;
        });

        return [
            'success' => true
        ];
    }

    public function getResultGenerator($entity, $filters = [])
    {
        if ($entity == 'product')
        {
            return $this->products->toArray();
        }

        return [];
    }

    public function getUser($iduser)
    {
        $user = $this->users->first(function (array $user) use ($iduser) {
            return Arr::get($user, 'iduser') == $iduser;
        });

        return [
            'success' => true,
            'data' => $user->toArray()
        ];
    }

    public function getProductWarehouseSettings($idproduct)
    {
        // TODO: Make stock level order variable: Task 19817: Zorg ervoor dat stock_level_order variable is in PicqerMock
        return [
            'success' => true,
            'data' => [
                [
                    'idproduct' => $idproduct,
                    'stock_level_order' => 3
                ]
            ]
        ];
    }

    public function addSupplier($params)
    {
        $supplier = new PicqerSupplier(Arr::get($params, 'name'), $this->idSupplierCount);
        $this->idSupplierCount++;

        $this->suppliers->push($supplier);

        return [
            'success' => true,
            'data' => $supplier->toArray()
        ];
    }

    public function getSupplier($idsupplier)
    {
        $supplier = $this->suppliers->first(function (PicqerSupplier $picqerSupplier) use ($idsupplier) {
            return $picqerSupplier->idSupplier() == $idsupplier;
        });

        return [
            'success' => true,
            'data' => $supplier->toArray()
        ];
    }

    public function getPurchaseorders($filters = [])
    {
        return [
            'success' => true,
            'data' => []
        ];
    }
}
