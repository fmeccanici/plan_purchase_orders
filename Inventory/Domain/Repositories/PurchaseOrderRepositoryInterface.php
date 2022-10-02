<?php

namespace App\Inventory\Domain\Repositories;

use App\Inventory\Domain\PurchaseOrders\PurchaseOrder;

interface PurchaseOrderRepositoryInterface
{
    public function findOneById(string $id): ?PurchaseOrder;
    public function save(PurchaseOrder $purchaseOrder): PurchaseOrder;
}
