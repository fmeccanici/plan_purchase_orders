<?php

namespace Tests\Unit\Warehouse\Mocks\Picqer\BatchPicklists;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;

class PicqerBatchPicklistPicklist implements Arrayable
{
    protected int $idPicklist;
    protected string $picklistId;
    protected ?string $reference;
    protected string $status;
    protected string $alias;
    protected ?PicqerPickingContainer $pickingContainer;
    protected int $totalProducts;
    protected string $deliveryName;
    protected int $totalCollected;
    protected bool $hasNotes;
    protected bool $hasCustomerRemarks;
    protected ?string $customerRemarks;
    protected CarbonImmutable $createdAt;

    /**
     * @param int $idPicklist
     * @param string $picklistId
     * @param string|null $reference
     * @param string $status
     * @param string $alias
     * @param int $totalProducts
     * @param string $deliveryName
     * @param int $totalCollected
     * @param bool $hasNotes
     * @param bool $hasCustomerRemarks
     * @param string|null $customerRemarks
     * @param CarbonImmutable $createdAt
     */
    public function __construct(int $idPicklist, string $picklistId, ?string $reference, string $status, string $alias, ?PicqerPickingContainer $pickingContainer, int $totalProducts, string $deliveryName, int $totalCollected, bool $hasNotes, bool $hasCustomerRemarks, ?string $customerRemarks, CarbonImmutable $createdAt)
    {
        $this->idPicklist = $idPicklist;
        $this->picklistId = $picklistId;
        $this->reference = $reference;
        $this->status = $status;
        $this->alias = $alias;
        $this->pickingContainer = $pickingContainer;
        $this->totalProducts = $totalProducts;
        $this->deliveryName = $deliveryName;
        $this->totalCollected = $totalCollected;
        $this->hasNotes = $hasNotes;
        $this->hasCustomerRemarks = $hasCustomerRemarks;
        $this->customerRemarks = $customerRemarks;
        $this->createdAt = $createdAt;
    }

    public function toArray()
    {
        return [
            'idpicklist' => $this->idPicklist,
            'picklistid' => $this->picklistId,
            'reference' => $this->reference,
            'status' => $this->status,
            'alias' => $this->alias,
            'picking_container' => $this->pickingContainer?->toArray(),
            'total_products' => $this->totalProducts,
            'delivery_name' => $this->deliveryName,
            'total_collected' => $this->totalCollected,
            'has_notes' => $this->hasNotes,
            'has_customer_remarks' => $this->hasCustomerRemarks,
            'customer_remarks' => $this->customerRemarks,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s')
        ];
    }
}
