<?php

namespace App\Inventory\Infrastructure\Persistence\InMemory\Repositories;

use App\Inventory\Domain\PurchaseTasks\PurchaseTask;
use Illuminate\Support\Collection;

class InMemoryCollectionPurchaseTaskRepository implements \App\Inventory\Domain\Repositories\PurchaseTaskRepositoryInterface
{
    protected Collection $purchaseOrderTasks;
    protected int $nextIdentity;

    public function __construct()
    {
        $this->purchaseOrderTasks = collect();
        $this->nextIdentity = 0;
    }

    public function findOneByPurchaseScheduleId(string $purchaseScheduleId): ?PurchaseTask
    {
        return $this->purchaseOrderTasks->first(function (PurchaseTask $purchaseOrderTask) use ($purchaseScheduleId) {
            return $purchaseOrderTask->purchaseScheduleId() == $purchaseScheduleId;
        });
    }

    public function save(PurchaseTask $purchaseOrderTask): PurchaseTask
    {
        $purchaseOrderTask = $this->setIdentity($purchaseOrderTask);
        $this->purchaseOrderTasks->push($purchaseOrderTask);
        return $purchaseOrderTask;
    }

    private function setIdentity(PurchaseTask $purchaseOrderTask): PurchaseTask
    {
        $purchaseOrderTask->setIdentity($this->nextIdentity);
        $this->nextIdentity ++;
        return $purchaseOrderTask;
    }

    public function delete(PurchaseTask $purchaseOrderTask): void
    {
        $purchaseOrderTaskId = $purchaseOrderTask->identity();
        $this->purchaseOrderTasks = $this->purchaseOrderTasks->filter(function (PurchaseTask $purchaseOrderTask) use ($purchaseOrderTaskId) {
            return $purchaseOrderTask->identity() != $purchaseOrderTaskId;
        });
    }
}
