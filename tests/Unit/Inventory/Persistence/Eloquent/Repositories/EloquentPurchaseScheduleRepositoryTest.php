<?php

namespace Tests\Unit\Inventory\Infrastructure\Persistence\Eloquent\Repositories;

use App\Inventory\Domain\PurchaseSchedules\CreatePurchaseSchedule;
use App\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentPurchaseScheduleRepository;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class EloquentPurchaseScheduleRepositoryTest extends TestCase
{
    use DatabaseMigrations;

    protected EloquentPurchaseScheduleRepository $purchaseScheduleRepository;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->purchaseScheduleRepository = new EloquentPurchaseScheduleRepository();
    }

    /** @test */
    public function it_should_save_purchase_schedule()
    {
        // Given
        $purchaseSchedule = CreatePurchaseSchedule::one([
            'supplier_id' => 0,
            'tag' => 'Onderhoudsproducten'
        ]);

        // When
        $purchaseSchedule = $this->purchaseScheduleRepository->save($purchaseSchedule);

        // Then
        $foundPurchaseSchedule = $this->purchaseScheduleRepository->findOneById($purchaseSchedule->identity());

        self::assertEquals($purchaseSchedule, $foundPurchaseSchedule);
    }
}
