<?php

namespace Tests\Unit\Warehouse\Mocks\Picqer\BatchPicklists;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;

class PicqerBatchPicklistReturnedFromGetAllBatchPicklists implements Arrayable
{
    protected int $idPicklistBatch;
    protected int $idWarehouse;
    protected int $picklistBatchId;
    protected string $type;
    protected string $status;
    protected PicqerAssignedTo $assignedTo;
    protected ?string $completedBy;
    protected int $totalProducts;
    protected int $totalPicklists;
    protected CarbonImmutable $completedAt;
    protected CarbonImmutable $createdAt;
    protected CarbonImmutable $updatedAt;

    /**
     * @param int $idPicklistBatch
     * @param int $idWarehouse
     * @param int $picklistBatchId
     * @param string $type
     * @param string $status
     * @param PicqerAssignedTo $assignedTo
     * @param string|null $completedBy
     * @param int $totalProducts
     * @param int $totalPicklists
     * @param CarbonImmutable $completedAt
     * @param CarbonImmutable $createdAt
     * @param CarbonImmutable $updatedAt
     */
    public function __construct(int $idPicklistBatch, int $idWarehouse, int $picklistBatchId, string $type, string $status, PicqerAssignedTo $assignedTo, ?string $completedBy, int $totalProducts, int $totalPicklists, CarbonImmutable $completedAt, CarbonImmutable $createdAt, CarbonImmutable $updatedAt)
    {
        $this->idPicklistBatch = $idPicklistBatch;
        $this->idWarehouse = $idWarehouse;
        $this->picklistBatchId = $picklistBatchId;
        $this->type = $type;
        $this->status = $status;
        $this->assignedTo = $assignedTo;
        $this->completedBy = $completedBy;
        $this->totalProducts = $totalProducts;
        $this->totalPicklists = $totalPicklists;
        $this->completedAt = $completedAt;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function toArray()
    {
        return [
            'idpicklist_batch' => $this->idPicklistBatch,
            'idwarehouse' => $this->idWarehouse,
            'picklist_batchid' => $this->picklistBatchId,
            'type' => $this->type,
            'assigned_to' => $this->assignedTo,
            'completed_by' => $this->completedBy,
            'total_products' => $this->totalProducts,
            'total_picklists' => $this->totalPicklists,
            'completed_at' => $this->completedAt->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
