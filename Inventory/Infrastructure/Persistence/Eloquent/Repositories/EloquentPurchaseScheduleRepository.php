<?php

namespace App\Inventory\Infrastructure\Persistence\Eloquent\Repositories;

use App\Inventory\Domain\PurchaseSchedules\PurchaseSchedule;
use App\Inventory\Domain\Repositories\PurchaseScheduleRepositoryInterface;
use App\Inventory\Infrastructure\Persistence\Eloquent\PurchaseSchedules\EloquentPurchaseSchedule;
use App\Inventory\Infrastructure\Persistence\Eloquent\PurchaseSchedules\EloquentPurchaseScheduleMapper;
use Illuminate\Support\Collection;

class EloquentPurchaseScheduleRepository implements PurchaseScheduleRepositoryInterface
{

    public function findOneBySupplierIdAndTag(string $supplierId, string $tag): ?PurchaseSchedule
    {
        $model = EloquentPurchaseSchedule::query()
                    ->where([
                        'supplier_id' => $supplierId,
                        'tag' => $tag
                    ])
                    ->take(1)
                    ->get()
                    ->first();

        return EloquentPurchaseScheduleMapper::reconstituteEntity($model);
    }

    public function save(PurchaseSchedule $purchaseSchedule): PurchaseSchedule
    {
        $model = EloquentPurchaseSchedule::query()
            ->where('id', $purchaseSchedule->identity())
            ->take(1)
            ->get()
            ->first();

        EloquentPurchaseScheduleMapper::pruneModel($purchaseSchedule, $model);
        EloquentPurchaseScheduleMapper::createOrUpdateModel($purchaseSchedule, $model);

        return $purchaseSchedule;
    }

    public function findOneById(string $id): ?PurchaseSchedule
    {
        $model = EloquentPurchaseSchedule::query()
            ->where('id', $id)
            ->take(1)
            ->get()
            ->first();

        return EloquentPurchaseScheduleMapper::reconstituteEntity($model);
    }

    public function findAll(): Collection
    {
        return EloquentPurchaseScheduleMapper::reconstituteEntities(EloquentPurchaseSchedule::all());
    }
}
