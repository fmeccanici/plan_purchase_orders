<?php

namespace App\Inventory\Domain\Services;

use App\Inventory\Domain\PurchaseTasks\PurchaseTask;

interface PurchaseTaskExecutionServiceInterface
{
    public function execute(PurchaseTask $purchaseOrderTask): void;
}
