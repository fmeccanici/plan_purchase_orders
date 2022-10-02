<?php

namespace App\Inventory\Domain\PurchaseSchedules;

use Illuminate\Support\Arr;

class CreatePurchaseSchedule
{
    public static function one(array $attributes = []): PurchaseSchedule
    {
        $supplierId = Arr::get($attributes, 'supplier_id');
        $tag = Arr::get($attributes, 'tag');

        $monday = new PurchaseMoment(11, 0, PurchaseMoment::MONDAY);
        $tuesday = new PurchaseMoment(11, 0, PurchaseMoment::TUESDAY);
        $wednesday = new PurchaseMoment(11, 0, PurchaseMoment::WEDNESDAY);
        $thursday = new PurchaseMoment(11, 0, PurchaseMoment::THURSDAY);
        $friday = new PurchaseMoment(11, 0, PurchaseMoment::FRIDAY);
        $saturday = new PurchaseMoment(11, 0, PurchaseMoment::SATURDAY);
        $sunday = new PurchaseMoment(11, 0, PurchaseMoment::SUNDAY);
        $defaultPurchaseMoments = collect([$monday, $tuesday, $wednesday, $thursday, $friday, $saturday, $sunday]);
        $purchaseMoments = Arr::get($attributes, 'purchase_moments', $defaultPurchaseMoments);

        return new PurchaseSchedule($supplierId, $tag, $purchaseMoments);
    }
}
