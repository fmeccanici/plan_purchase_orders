<?php

namespace App\Inventory\Domain\Services;

use App\Inventory\Domain\PurchaseOrders\PurchaseOrder;
use App\Inventory\Domain\PurchaseOrders\PurchaseOrderTemplate;
use App\Inventory\Domain\Suppliers\Supplier;

interface PurchaseOrderTemplateManagerInterface
{
    public function determineTemplate(PurchaseOrder $purchaseOrder, Supplier $supplier, string $tag): PurchaseOrderTemplate;
}
