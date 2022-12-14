<?php

namespace Tests\Unit\Inventory\Infrastructure\Persistence\Eloquent\Repositories;

use App\Inventory\Domain\PurchaseTasks\PurchaseTask;
use App\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentPurchaseTaskRepository;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class EloquentPurchaseOrderTaskRepositoryTest extends TestCase
{
    use DatabaseMigrations;

    protected EloquentPurchaseTaskRepository $purchaseOrderTaskRepository;
    protected int $purchaseScheduleId;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->purchaseOrderTaskRepository = new EloquentPurchaseTaskRepository();
        $this->purchaseScheduleId = 0;
    }

    /** @test */
    public function it_should_add_purchase_order_task()
    {
        // Given
        $purchaseOrderTask = new PurchaseTask($this->purchaseScheduleId, CarbonImmutable::now()->startOfDay());

        // When
        $purchaseOrderTask = $this->purchaseOrderTaskRepository->save($purchaseOrderTask);

        // Then
        $foundPurchaseOrderTask = $this->purchaseOrderTaskRepository->findOneByPurchaseScheduleId($this->purchaseScheduleId);

        self::assertEquals($purchaseOrderTask, $foundPurchaseOrderTask);
    }

    /** @test */
    public function it_should_delete_purchase_order_task()
    {
        // Given
        $purchaseOrderTask = new PurchaseTask($this->purchaseScheduleId, CarbonImmutable::now()->startOfDay());
        $purchaseOrderTask = $this->purchaseOrderTaskRepository->save($purchaseOrderTask);

        // When
        $this->purchaseOrderTaskRepository->delete($purchaseOrderTask);

        // Then
        self::assertNull($this->purchaseOrderTaskRepository->findOneByPurchaseScheduleId($this->purchaseScheduleId));
    }
}
