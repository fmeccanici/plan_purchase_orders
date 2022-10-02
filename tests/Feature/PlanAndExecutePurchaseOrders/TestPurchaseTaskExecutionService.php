<?php

namespace Tests\Feature\Inventory\PlanAndExecutePurchaseOrders;

use App\Inventory\Domain\PurchaseTasks\PurchaseTask;
use Illuminate\Support\Collection;

class TestPurchaseTaskExecutionService implements \App\Inventory\Domain\Services\PurchaseTaskExecutionServiceInterface
{
    protected Collection $executedPurchaseOrderTasks;

    public function __construct()
    {
        $this->executedPurchaseOrderTasks = collect();
    }

    public function execute(PurchaseTask $purchaseOrderTask): void
    {
        $this->executedPurchaseOrderTasks->push($purchaseOrderTask);
    }

    public function executedPurchaseOrderTasks(): Collection
    {
        return $this->executedPurchaseOrderTasks;
    }
}
