<?php

namespace Tests\Unit\Inventory\Infrastructure\Persistence\InMemory;

use App\Inventory\Domain\InventoryItems\InventoryItem;
use App\Inventory\Domain\InventoryItems\InventoryItemFactory;
use App\Inventory\Infrastructure\Persistence\InMemory\Repositories\InMemoryCollectionInventoryItemRepository;
use Tests\TestCase;

class InMemoryCollectionInventoryItemRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->inventoryItemRepository = new InMemoryCollectionInventoryItemRepository();
    }

    /** @test */
    public function it_should_find_all_inventory_items()
    {
        // Given
        $inventoryItems = InventoryItemFactory::createMultiple(10);

        // When
        $this->inventoryItemRepository->saveMultiple($inventoryItems);

        // Then
        $foundInventoryItems = $this->inventoryItemRepository->findAll();
        self::assertEquals($inventoryItems->toArray(), $foundInventoryItems->toArray());
    }

    /** @test */
    public function it_should_find_all_inventory_items_by_registration_numbers()
    {
        // Given
        $inventoryItems = InventoryItemFactory::createMultiple(10);
        $registrationNumbers = $inventoryItems->map(function (InventoryItem $inventoryItem) {
            return $inventoryItem->registrationNumber();
        });

        // When
        $this->inventoryItemRepository->saveMultiple($inventoryItems);

        // Then
        $foundInventoryItems = $this->inventoryItemRepository->findAllByRegistrationNumbers($registrationNumbers->toArray());
        self::assertEquals($inventoryItems->toArray(), $foundInventoryItems->toArray());
    }

    /** @test */
    public function it_should_list_all_inventory_items()
    {
        // Given
        $inventoryItems = InventoryItemFactory::createMultiple(10);

        // When
        $this->inventoryItemRepository->saveMultiple($inventoryItems);

        // Then
        $foundInventoryItems = $this->inventoryItemRepository->list();

        // TODO: Task 18939: Stem de list() functie return type af met de front end, zodat we het domein object kunnen returnen
        self::assertEquals($inventoryItems->count(), sizeof($foundInventoryItems->items()));
    }

    /** @test */
    public function it_should_add_one_inventory_item()
    {
        // Given
        $inventoryItem = InventoryItemFactory::create();

        // When
        $this->inventoryItemRepository->save($inventoryItem);

        // Then
        $foundInventoryItems = $this->inventoryItemRepository->findAll();
        self::assertEquals(1, $foundInventoryItems->count());
        self::assertEquals($inventoryItem->toArray(), $foundInventoryItems->first()->toArray());
    }

    /** @test */
    public function it_should_find_inventory_item_by_registration_number()
    {
        // Given
        $inventoryItems = InventoryItemFactory::createMultiple(10);
        $this->inventoryItemRepository->saveMultiple($inventoryItems);

        // When
        $foundInventoryItem = $this->inventoryItemRepository->findOneByRegistrationNumber($inventoryItems->first()->registrationNumber());

        // Then
        self::assertNotNull($foundInventoryItem);
        self::assertEquals($inventoryItems->first()->toArray(), $foundInventoryItem->toArray());
    }

    /** @test */
    public function it_should_find_inventory_items_by_registration_numbers()
    {
        // Given
        $inventoryItems = InventoryItemFactory::createMultiple(10);
        $this->inventoryItemRepository->saveMultiple($inventoryItems);
        $registrationNumbers = $inventoryItems->map(function (InventoryItem $inventoryItem) {
            return $inventoryItem->registrationNumber();
        });

        // When
        // TODO: Task 18940: Denk na over het gebruik van ID's uit de database op het domein object
        $foundInventoryItems = $this->inventoryItemRepository->findAllByRegistrationNumbers($registrationNumbers->slice(0, 3)->toArray());

        // Then
        self::assertNotNull($foundInventoryItems);
        self::assertEquals($inventoryItems->slice(0, 3)->toArray(), $foundInventoryItems->toArray());
    }

    /** @test */
    public function it_should_update_inventory_item()
    {
        // Given
        $inventoryItems = InventoryItemFactory::createMultiple(10);
        $this->inventoryItemRepository->saveMultiple($inventoryItems);

        // When
        $updatedInventoryItem = InventoryItemFactory::create([
            'registrationNumber' => $inventoryItems->first()->registrationNumber(),
            'productCode' => $inventoryItems->first()->productCode()
        ]);

        $this->inventoryItemRepository->update($updatedInventoryItem);

        // Then
        $foundInventoryItem = $this->inventoryItemRepository->findOneByProductCode($updatedInventoryItem->productCode());
        self::assertEquals($updatedInventoryItem->toArray(), $foundInventoryItem->toArray());
    }

    /** @test */
    public function it_should_remove_inventory_items_by_registration_numbers()
    {
        // Given
        $inventoryItems = InventoryItemFactory::createMultiple(10);
        $this->inventoryItemRepository->saveMultiple($inventoryItems);
        $registrationNumbers = $inventoryItems->slice(0, 3)->map->registrationNumber()->toArray();

        // When
        $this->inventoryItemRepository->removeByRegistrationNumbers($registrationNumbers);

        // Then
        $foundInventoryItems = $this->inventoryItemRepository->findAll();

        // Remove first 3 items from initial collection
        $inventoryItems->shift(3);
        self::assertEquals(7, $foundInventoryItems->count());
        self::assertEquals($inventoryItems->toArray(), $foundInventoryItems->toArray());
    }
}
