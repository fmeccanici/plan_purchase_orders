<?php

namespace App\Inventory\Domain\Services;

use App\Inventory\Domain\Mails\PurchaseOrderMail;
use App\Inventory\Domain\PurchaseOrders\PurchaseOrder;
use App\Inventory\Domain\Suppliers\Supplier;

interface PurchaseOrderMailManagerInterface
{
    public function determineMail(Supplier $supplier, string $tag, string $purchaseOrderExportUrl, PurchaseOrder $purchaseOrder): PurchaseOrderMail;
}
