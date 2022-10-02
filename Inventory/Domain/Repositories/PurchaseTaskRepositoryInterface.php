<?php

namespace App\Inventory\Domain\Repositories;

use App\Inventory\Domain\PurchaseTasks\PurchaseTask;

interface PurchaseTaskRepositoryInterface
{
    public function findOneByPurchaseScheduleId(string $purchaseScheduleId): ?PurchaseTask;
    public function save(PurchaseTask $purchaseOrderTask): PurchaseTask;

    public function delete(PurchaseTask $purchaseOrderTask): void;
}
