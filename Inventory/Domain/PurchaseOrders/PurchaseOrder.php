<?php

namespace App\Inventory\Domain\PurchaseOrders;

use App\Inventory\Domain\Exceptions\PurchaseStatusException;
use App\SharedKernel\CleanArchitecture\AggregateRoot;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

class PurchaseOrder extends AggregateRoot implements Arrayable
{
    protected Collection $purchaseOrderLines;
    protected string $supplierId;
    protected string $remarks;
    protected PurchaseOrderStatus $status;
    protected ?string $reference;

    /**
     * @param Collection $purchaseOrderLines
     * @param string $supplierId
     * @param PurchaseOrderStatus $status
     * @param string $remarks
     * @param string|null $reference
     */
    public function __construct(Collection $purchaseOrderLines, string $supplierId, PurchaseOrderStatus $status, string $remarks = '', ?string $reference = null)
    {
        $this->purchaseOrderLines = $purchaseOrderLines;
        $this->supplierId = $supplierId;
        $this->remarks = $remarks;
        $this->status = $status;
        $this->reference = $reference;
    }

    public function addPurchaseOrderLine(PurchaseOrderLine $purchaseOrderLine): Collection
    {
        $this->purchaseOrderLines->push($purchaseOrderLine);
        return $this->purchaseOrderLines;
    }

    public function purchaseOrderLines(): Collection
    {
        return $this->purchaseOrderLines;
    }

    public function purchased(): bool
    {
        return $this->status->purchased();
    }

    public function concept(): bool
    {
        return $this->status->concept();
    }

    /**
     * @throws PurchaseStatusException
     */
    public function changeStatus(string $status)
    {
        $this->status = new PurchaseOrderStatus($status);
    }

    public function supplierId(): string
    {
        return $this->supplierId;
    }

    public function remarks(): string
    {
        return $this->remarks;
    }

    protected function cascadeSetIdentity(int|string $id): void
    {
        $this->purchaseOrderLines->each(function (PurchaseOrderLine $x) use ($id) {
            $x->setParentIdentity($id);
        });
    }

    public function externalLink(): string
    {
        return sprintf('https://%s.picqer.com/purchaseorders/%s', config('picqer.subdomain'), $this->identity());
    }

    public function totalAmountInEuros(): float
    {
        return $this->purchaseOrderLines->sum(function (PurchaseOrderLine $purchaseOrderLine) {
            return $purchaseOrderLine->quantity() * $purchaseOrderLine->price();
        });
    }

    public function deliveryDate(): ?CarbonImmutable
    {
        return $this->purchaseOrderLines->max(function (PurchaseOrderLine $purchaseOrderLine) {
            return $purchaseOrderLine->deliveryDate();
        });
    }

    public function reference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference)
    {
        $this->reference = $reference;
    }

    public function toArray()
    {
        return [
            'id' => $this->identity(),
            'purchase_order_lines' => $this->purchaseOrderLines->toArray(),
            'supplier_id' => $this->supplierId,
            'remarks' => $this->remarks,
            'status' => $this->status->toArray(),
            'delivery_date' => $this->deliveryDate()?->toDateString(),
            'reference' => $this->reference
        ];
    }
}
