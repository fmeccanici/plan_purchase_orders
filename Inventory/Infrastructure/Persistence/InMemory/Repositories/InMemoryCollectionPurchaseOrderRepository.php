<?php

namespace App\Inventory\Infrastructure\Persistence\InMemory\Repositories;

use App\Inventory\Domain\PurchaseOrders\PurchaseOrder;
use Illuminate\Support\Collection;

class InMemoryCollectionPurchaseOrderRepository implements \App\Inventory\Domain\Repositories\PurchaseOrderRepositoryInterface
{
    protected Collection $purchaseOrders;
    protected int $purchaseOrderId;
    protected ?string $reference;

    public function __construct()
    {
        $this->purchaseOrders = collect();
        $this->purchaseOrderId = 0;
        $this->reference = null;
    }

    public function findOneById(string $id): ?PurchaseOrder
    {
        return $this->purchaseOrders->first(function (PurchaseOrder $purchaseOrder) use ($id) {
            return $id == $purchaseOrder->identity();
        });
    }

    public function save(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        $this->delete($purchaseOrder);

        $this->setIdentity($purchaseOrder);
        $this->purchaseOrders->push($purchaseOrder);
        return $purchaseOrder;
    }

    private function setIdentity(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        $this->purchaseOrderId ++;
        $this->reference = uniqid();

        $purchaseOrder->setIdentity($this->purchaseOrderId);
        $purchaseOrder->setReference($this->reference);

        return $purchaseOrder;
    }

    private function delete(PurchaseOrder $purchaseOrder): void
    {
        $purchaseOrderId = $purchaseOrder->identity();

        $this->purchaseOrders = $this->purchaseOrders->filter(function (PurchaseOrder $purchaseOrder) use ($purchaseOrderId) {
                return $purchaseOrder->identity() != $purchaseOrderId;
        });
    }

    public function currentReference(): string
    {
        return $this->reference;
    }

    public function currentId(): int
    {
        return $this->purchaseOrderId;
    }
}
