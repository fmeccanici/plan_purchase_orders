<?php

namespace App\Inventory\Domain\Repositories;

use App\Inventory\Domain\PurchaseSchedules\PurchaseSchedule;
use Illuminate\Support\Collection;

interface PurchaseScheduleRepositoryInterface
{
    public function findOneById(string $id): ?PurchaseSchedule;
    public function findOneBySupplierIdAndTag(string $supplierId, string $tag): ?PurchaseSchedule;
    public function save(PurchaseSchedule $purchaseSchedule): PurchaseSchedule;
    public function findAll(): Collection;
}
