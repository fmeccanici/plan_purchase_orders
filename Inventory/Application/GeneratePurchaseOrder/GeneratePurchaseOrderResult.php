<?php


namespace App\Inventory\Application\GeneratePurchaseOrder;


use App\Inventory\Domain\PurchaseOrders\PurchaseOrder;
use App\Inventory\Domain\PurchaseOrders\PurchaseOrderExport;

final class GeneratePurchaseOrderResult
{
    protected PurchaseOrderExport $purchaseOrderExport;
    protected PurchaseOrder $purchaseOrder;

    /**
     * @param PurchaseOrderExport $purchaseOrderExport
     * @param PurchaseOrder $purchaseOrder
     */
    public function __construct(PurchaseOrderExport $purchaseOrderExport, PurchaseOrder $purchaseOrder)
    {
        $this->purchaseOrderExport = $purchaseOrderExport;
        $this->purchaseOrder = $purchaseOrder;
    }

    public function purchaseOrderExport(): PurchaseOrderExport
    {
        return $this->purchaseOrderExport;
    }

    public function purchaseOrder(): PurchaseOrder
    {
        return $this->purchaseOrder;
    }
}
