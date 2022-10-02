<?php

namespace Tests\Feature\Inventory\PlanAndExecutePurchaseOrders;

use App\Inventory\Domain\PurchaseSchedules\CreatePurchaseSchedule;
use App\Inventory\Domain\PurchaseSchedules\PurchaseMoment;
use App\Inventory\Domain\PurchaseTasks\PurchaseTask;
use App\Inventory\Domain\Repositories\PurchaseScheduleRepositoryInterface;
use App\Inventory\Domain\Repositories\PurchaseTaskRepositoryInterface;
use App\Inventory\Domain\Repositories\SupplierRepositoryInterface;
use App\Inventory\Domain\Services\PurchaseTaskExecutionServiceInterface;
use App\Inventory\Domain\Suppliers\CreateSupplier;
use App\Inventory\Domain\Suppliers\Supplier;
use App\Inventory\Infrastructure\Persistence\InMemory\Repositories\InMemoryCollectionPurchaseScheduleRepository;
use App\Inventory\Infrastructure\Persistence\InMemory\Repositories\InMemoryCollectionPurchaseTaskRepository;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Mockery\MockInterface;
use Tests\TestCase;

class PlanAndExecutePurchaseOrdersTest extends TestCase
{
    protected InMemoryCollectionPurchaseScheduleRepository $purchaseScheduleRepository;
    protected TestPurchaseTaskExecutionService $purchaseOrderTaskExecutionService;
    protected Supplier $supplier;
    protected string $supplierId;
    protected InMemoryCollectionPurchaseTaskRepository $purchaseOrderTaskRepository;
    protected PurchaseMoment $monday;
    protected int $hour;
    protected int $minute;
    protected int $day;
    protected int $dayOfWeek;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->purchaseScheduleRepository = new InMemoryCollectionPurchaseScheduleRepository();
        $this->app->bind(PurchaseScheduleRepositoryInterface::class, function () {return $this->purchaseScheduleRepository;});

        $this->purchaseOrderTaskExecutionService = new TestPurchaseTaskExecutionService();
        $this->app->bind(PurchaseTaskExecutionServiceInterface::class, function () {return $this->purchaseOrderTaskExecutionService;});

        $this->purchaseOrderTaskRepository = new InMemoryCollectionPurchaseTaskRepository();
        $this->app->bind(PurchaseTaskRepositoryInterface::class, function () {return $this->purchaseOrderTaskRepository;});

        $this->supplierId = '1';
        $this->supplier = CreateSupplier::peitsman();
        $this->supplier->setIdentity($this->supplierId);

        $supplierRepositoryMock = $this->mock(SupplierRepositoryInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('findOneById')
                ->andReturn($this->supplier);
            $mock->shouldReceive('searchOneByName')
                ->andReturn($this->supplier);
        });

        $this->app->bind(SupplierRepositoryInterface::class, function () use ($supplierRepositoryMock) {return $supplierRepositoryMock;});

        $this->dayOfWeek = CarbonImmutable::MONDAY;
        $this->hour = 11;
        $this->minute = 0;
        $this->monday = new PurchaseMoment($this->hour, $this->minute, PurchaseMoment::MONDAY, PurchaseMoment::FRIDAY);
    }

    /** @test */
    public function it_should_save_purchase_order_task()
    {
        // Given
        $this->withoutExceptionHandling();
        $now = CarbonImmutable::now();
        if (! $now->isDayOfWeek(CarbonImmutable::MONDAY))
        {
            $now = $now->next(CarbonImmutable::MONDAY);
        }

        CarbonImmutable::setTestNow($now->setHour($this->hour - 1)->setMinute($this->minute));

        $url = route('plan-purchase-orders');
        $tag = 'Onderhoudsproducten';
        $purchaseSchedule = CreatePurchaseSchedule::one([
            'supplier_id' => $this->supplierId,
            'tag' => $tag,
            'purchase_moments' => collect([$this->monday])
        ]);

        $this->purchaseScheduleRepository->save($purchaseSchedule);

        // When
        $response = $this->post($url, [
            'all_suppliers_and_tags' => false,
            'suppliers' => [
                [
                    'id' => $this->supplierId,
                    'tags' => [
                        $tag
                    ]
                ]
            ]
        ]);

        // Then
        $expectedPlannedAt = $now->setHour($this->hour)->setMinute($this->minute);

        self::assertEquals(new PurchaseTask($purchaseSchedule->identity(), $expectedPlannedAt),
            $this->purchaseOrderTaskRepository->findOneByPurchaseScheduleId($purchaseSchedule->identity()));
        self::assertEmpty($this->purchaseOrderTaskExecutionService->executedPurchaseOrderTasks());
    }

    /** @test */
    public function it_should_execute_and_delete_already_planned_purchase_order_task()
    {
        // Given
        $this->withoutExceptionHandling();
        $now = CarbonImmutable::now()->next(Carbon::MONDAY)->setHour($this->hour)->setMinute($this->minute);
        CarbonImmutable::setTestNow(CarbonImmutable::now()->next(Carbon::MONDAY)->setHour($this->hour)->setMinute($this->minute));

        $url = route('plan-purchase-orders');
        $tag = 'Onderhoudsproducten';
        $purchaseSchedule = CreatePurchaseSchedule::one([
            'supplier_id' => $this->supplierId,
            'tag' => $tag,
            'monday' => $this->monday
        ]);

        $this->purchaseScheduleRepository->save($purchaseSchedule);

        $purchaseOrderTask = new PurchaseTask($purchaseSchedule->identity(), $now);
        $this->purchaseOrderTaskRepository->save($purchaseOrderTask);

        // When
        $response = $this->post($url, [
            'all_suppliers_and_tags' => false,
            'suppliers' => [
                [
                    'id' => $this->supplierId,
                    'tags' => [
                        $tag
                    ]
                ]
            ]
        ]);

        // Then
        self::assertEquals($purchaseOrderTask, $this->purchaseOrderTaskExecutionService->executedPurchaseOrderTasks()->first());
        self::assertNull($this->purchaseOrderTaskRepository->findOneByPurchaseScheduleId($purchaseSchedule->identity()));
    }

    /** @test */
    public function it_should_plan_task_but_do_not_execute()
    {
        // Given
        $now = CarbonImmutable::now()->next(Carbon::MONDAY)->setHour($this->hour + 1)->setMinute($this->minute);
        CarbonImmutable::setTestNow($now);

        $url = route('plan-purchase-orders');
        $tag = 'Onderhoudsproducten';
        $purchaseSchedule = CreatePurchaseSchedule::one([
            'supplier_id' => $this->supplierId,
            'tag' => $tag,
            'purchase_moments' => collect([new PurchaseMoment($this->hour, $this->minute, PurchaseMoment::MONDAY, PurchaseMoment::FRIDAY), new PurchaseMoment(17, 0, PurchaseMoment::MONDAY, PurchaseMoment::FRIDAY)
            ])
        ]);

        $this->purchaseScheduleRepository->save($purchaseSchedule);

        // When
        $response = $this->post($url, [
            'all_suppliers_and_tags' => false,
            'suppliers' => [
                [
                    'id' => $this->supplierId,
                    'tags' => [
                        $tag
                    ]
                ]
            ]
        ]);

        // Then
        self::assertEmpty($this->purchaseOrderTaskExecutionService->executedPurchaseOrderTasks()->first());
        self::assertEquals(new PurchaseTask($purchaseSchedule->identity(), $now->setHour(17)->setMinute(0)), $this->purchaseOrderTaskRepository->findOneByPurchaseScheduleId($purchaseSchedule->identity()));
    }

    /** @test */
    public function it_should_delete_task_if_tomorrow_and_task_is_not_executed()
    {
        // Given
        $this->withoutExceptionHandling();
        $now = CarbonImmutable::now()->next(Carbon::TUESDAY)->setHour($this->hour)->setMinute($this->minute);
        CarbonImmutable::setTestNow($now);

        $url = route('plan-purchase-orders');
        $tag = 'Onderhoudsproducten';
        $purchaseSchedule = CreatePurchaseSchedule::one([
            'supplier_id' => $this->supplierId,
            'tag' => $tag,
            'purchase_moments' => collect([new PurchaseMoment($this->hour, $this->minute, PurchaseMoment::MONDAY, PurchaseMoment::FRIDAY), new PurchaseMoment(17, 0, PurchaseMoment::MONDAY, PurchaseMoment::FRIDAY)])
        ]);

        $this->purchaseScheduleRepository->save($purchaseSchedule);

        $purchaseOrderTask = new PurchaseTask($purchaseSchedule->identity(), $now->subDay());
        $this->purchaseOrderTaskRepository->save($purchaseOrderTask);

        // When
        $response = $this->post($url, [
            'all_suppliers_and_tags' => false,
            'suppliers' => [
                [
                    'id' => $this->supplierId,
                    'tags' => [
                        $tag
                    ]
                ]
            ]
        ]);

        // Then
        self::assertEmpty($this->purchaseOrderTaskExecutionService->executedPurchaseOrderTasks()->first());
        self::assertNull($this->purchaseOrderTaskRepository->findOneByPurchaseScheduleId($purchaseSchedule->identity()));
    }
}