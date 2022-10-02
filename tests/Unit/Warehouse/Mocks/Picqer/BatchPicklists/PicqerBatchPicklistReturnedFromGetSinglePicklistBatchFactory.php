<?php

namespace Tests\Unit\Warehouse\Mocks\Picqer\BatchPicklists;

use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;

class PicqerBatchPicklistReturnedFromGetSinglePicklistBatchFactory
{
    public static function create(int $amount = 1, array $attributes = []): PicqerBatchPicklistReturnedFromGetSinglePicklistBatch
    {
        $idPicklistBatch = 2488779;
        $idWarehouse = 7053;
        $picklistBatchId = 6;
        $type = 'normal';
        $status = 'open';
        $assignedTo = new PicqerAssignedTo(15284, 'Floris Meccanici', 'floris');
        $completedBy = null;
        $totalProducts = 1;
        $totalPicklists = 1;
        $completedAt = null;
        $createdAt = CarbonImmutable::now();
        $updatedAt = CarbonImmutable::now();
        $product = new PicqerBatchPicklistProduct(24124483, 'Eindproduct A', 'Eindproduct A', '', null, [],
                                                    collect([new PicqerProductField(3209, 'Basisproduct', 'Extra veld: Basis Product A')]), '1.1',
                                                    collect([new PicqerBatchPicklistPicklistInsideProducts(61194583, 1, 0, 0)]));
        $products = collect([$product]);

        $pickingContainer = Arr::get($attributes, 'picking_container');
        if ($pickingContainer === 'null')
        {
            $pickingContainer = null;
        } else if ($pickingContainer === null)
        {
            $pickingContainer = new PicqerPickingContainer(uniqid());
        }

        $picklist = new PicqerBatchPicklistPicklist(61194583, "P2022-1515", null, 'new', 'A', $pickingContainer,
                                                    1, 'Floris Meccanici', 0, false, false, null, CarbonImmutable::now());
        $picklists = collect([$picklist]);

        return new PicqerBatchPicklistReturnedFromGetSinglePicklistBatch($idPicklistBatch, $idWarehouse, $picklistBatchId, $type, $status, $assignedTo,
                                                                            $completedBy, $totalProducts, $totalPicklists, $completedAt, $createdAt, $updatedAt, $products, $picklists);
    }
}
