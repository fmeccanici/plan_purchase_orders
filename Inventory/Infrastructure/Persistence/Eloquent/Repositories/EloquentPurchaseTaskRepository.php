<?php

namespace App\Inventory\Infrastructure\Persistence\Eloquent\Repositories;

use App\Inventory\Domain\PurchaseTasks\PurchaseTask;
use App\Inventory\Infrastructure\Persistence\Eloquent\PurchaseOrderTasks\EloquentPurchaseTask;
use App\Inventory\Infrastructure\Persistence\Eloquent\PurchaseOrderTasks\EloquentPurchaseTaskMapper;

class EloquentPurchaseTaskRepository implements \App\Inventory\Domain\Repositories\PurchaseTaskRepositoryInterface
{

    public function findOneByPurchaseScheduleId(string $purchaseScheduleId): ?PurchaseTask
    {
        $model = EloquentPurchaseTask::query()
            ->where('purchase_schedule_id', $purchaseScheduleId)
            ->take(1)
            ->get()
            ->first();

        return EloquentPurchaseTaskMapper::reconstituteEntity($model);
    }

    public function save(PurchaseTask $purchaseOrderTask): PurchaseTask
    {
        $model = EloquentPurchaseTask::query()
            ->where('id', $purchaseOrderTask->identity())
            ->take(1)
            ->get()
            ->first();

        EloquentPurchaseTaskMapper::pruneModel($purchaseOrderTask, $model);
        EloquentPurchaseTaskMapper::createOrUpdateModel($purchaseOrderTask, $model);

        return $purchaseOrderTask;
    }

    public function delete(PurchaseTask $purchaseOrderTask): void
    {
        $model = EloquentPurchaseTask::query()
            ->where('id', $purchaseOrderTask->identity())
            ->take(1)
            ->get()
            ->first();

        EloquentPurchaseTaskMapper::deleteModel($model);
    }
}
