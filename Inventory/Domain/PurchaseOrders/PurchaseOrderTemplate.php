<?php

namespace App\Inventory\Domain\PurchaseOrders;

use App\Inventory\Domain\Suppliers\Supplier;

abstract class PurchaseOrderTemplate
{
    protected PurchaseOrder $purchaseOrder;
    protected Supplier $supplier;

    public function __construct(PurchaseOrder $purchaseOrder,
                                Supplier $supplier)
    {
        $this->purchaseOrder = $purchaseOrder;
        $this->supplier = $supplier;
    }

    public function purchaseOrder(): PurchaseOrder
    {
        return $this->purchaseOrder;
    }

    public function supplier(): Supplier
    {
        return $this->supplier;
    }

    /**
     * @return PurchaseOrderExport The filepath and public URL of the exported template
     */
    abstract function export(PurchaseOrderTemplateSettings $purchaseOrderTemplateSettings): PurchaseOrderExport;
}
