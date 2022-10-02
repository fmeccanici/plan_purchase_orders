<?php

namespace App\Inventory\Domain\PurchaseOrders;

use Illuminate\Support\Arr;

class CreatePurchaseOrder
{
    public static function concept(array $attributes = []): PurchaseOrder
    {
        $purchaseOrderLines = Arr::get($attributes, 'purchase_order_lines', CreatePurchaseOrderLine::multiple(rand(1, 10)));
        $supplierId = Arr::get($attributes, 'supplier_id', 0);
        $remarks = Arr::get($attributes, 'remarks', '');

        $status = new PurchaseOrderStatus(PurchaseOrderStatus::CONCEPT);

        return new PurchaseOrder($purchaseOrderLines, $supplierId, $status, $remarks);
    }

    public static function purchased(array $attributes = []): PurchaseOrder
    {
        $purchaseOrderLines = Arr::get($attributes, 'purchase_order_lines', CreatePurchaseOrderLine::multiple(rand(1, 10)));
        $supplierId = Arr::get($attributes, 'supplier_id', 0);
        $remarks = Arr::get($attributes, 'remarks', '');

        $status = new PurchaseOrderStatus(PurchaseOrderStatus::PURCHASED);

        return new PurchaseOrder($purchaseOrderLines, $supplierId, $status, $remarks);
    }
}
