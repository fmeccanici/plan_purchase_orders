<?php

namespace App\Inventory\Domain\Services;

interface ProductCatalogServiceInterface
{
    public function getProduct(string $productCode): ?ProductCatalogProduct;
}
