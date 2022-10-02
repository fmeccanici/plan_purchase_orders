<?php

namespace App\Inventory\Infrastructure\Persistence\InMemory\Repositories;

use App\Inventory\Domain\PurchaseSchedules\PurchaseSchedule;
use Illuminate\Support\Collection;

class InMemoryCollectionPurchaseScheduleRepository implements \App\Inventory\Domain\Repositories\PurchaseScheduleRepositoryInterface
{
    protected Collection $purchaseSchedules;
    protected int $currentPurchaseScheduleId;

    public function __construct()
    {
        $this->purchaseSchedules = collect();
        $this->currentPurchaseScheduleId = 0;
    }

    public function findOneBySupplierIdAndTag(string $supplierId, string $tag): ?PurchaseSchedule
    {
        return $this->purchaseSchedules->first(function (PurchaseSchedule $purchaseSchedule) use ($supplierId, $tag) {
            return $purchaseSchedule->supplierId() === $supplierId && $purchaseSchedule->tag() === $tag;
        });
    }

    public function save(PurchaseSchedule $purchaseSchedule): PurchaseSchedule
    {
        $purchaseSchedule = $this->setIdentity($purchaseSchedule);
        $this->purchaseSchedules->push($purchaseSchedule);
        return $purchaseSchedule;
    }

    private function setIdentity(PurchaseSchedule $purchaseSchedule): PurchaseSchedule
    {
        $purchaseSchedule->setIdentity($this->currentPurchaseScheduleId);
        $this->currentPurchaseScheduleId ++;
        return $purchaseSchedule;
    }

    public function findOneById(string $id): ?PurchaseSchedule
    {
        return $this->purchaseSchedules->first(function (PurchaseSchedule $purchaseSchedule) use ($id) {
            return $id === $purchaseSchedule->identity();
        });
    }

    public function findAll(): Collection
    {
        return $this->purchaseSchedules;
    }
}
