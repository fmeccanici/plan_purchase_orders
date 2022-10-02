<?php

namespace Tests\Feature\Inventory\GeneratePurchaseOrder;

use App\Inventory\Domain\Services\ProductCatalogProduct;

class TestProductCatalogService implements \App\Inventory\Domain\Services\ProductCatalogServiceInterface
{

    public function getProduct(string $productCode): ProductCatalogProduct
    {
        return new ProductCatalogProduct($productCode, 'Test Product Code Supplier', 'Test Product Name');
    }
}
