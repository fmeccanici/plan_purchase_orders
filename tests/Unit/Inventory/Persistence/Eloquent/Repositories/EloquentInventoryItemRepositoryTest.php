<?php

namespace Tests\Unit\Inventory\Infrastructure\Persistence\Eloquent\Repositories;

use App\Inventory\Domain\InventoryItems\InventoryItem;
use App\Inventory\Domain\InventoryItems\InventoryItemFactory;
use App\Inventory\Domain\InventoryItems\Stock;
use App\Inventory\Infrastructure\Persistence\Eloquent\Repositories\EloquentInventoryItemRepository;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class EloquentInventoryItemRepositoryTest extends TestCase
{
    use DatabaseMigrations;

    private EloquentInventoryItemRepository $inventoryItemRepository;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->inventoryItemRepository = new EloquentInventoryItemRepository();
    }

    /** @test */
    public function it_should_list_inventory_items_with_specified_items_by_page_and_page()
    {
        // Given
        $itemsPerPage = 10;
        $page = 2;
        $listOptions = [];
        $listOptions['pagingOptions']['itemsPerPage'] = $itemsPerPage;
        $listOptions['pagingOptions']['page'] = $page;

        $inventoryItems = InventoryItemFactory::createMultiple(100, [
            'stock' => new Stock(1, 0, 0)
        ]);

        $this->inventoryItemRepository->saveMultiple($inventoryItems);

        // When
        $listedInventoryItems = $this->inventoryItemRepository->list($listOptions);

        // Then
        self::assertEquals($page, $listedInventoryItems->currentPage());
        self::assertEquals($itemsPerPage, $listedInventoryItems->perPage());

        // collect(items)->toArray() needed to use the toArray() method on InventoryItem,
        // otherwise it will return an array of InventoryItems
        // TODO: Task 18939: Stem de list() functie return type af met de front end, zodat we het domein object kunnen returnen
        self::assertEquals($inventoryItems->slice(10, 10)->values()->toArray(), collect($listedInventoryItems->items())->toArray());
    }

    /** @test */
    public function it_should_list_inventory_items_with_specified_items_by_page_and_page_2()
    {
        // Given
        $itemsPerPage = 10;
        $page = 1;
        $listOptions = [];
        $listOptions['pagingOptions']['itemsPerPage'] = $itemsPerPage;
        $listOptions['pagingOptions']['page'] = $page;

        $inventoryItems = InventoryItemFactory::createMultiple(100, [
            'stock' => new Stock(1, 0, 0)
        ]);
        $this->inventoryItemRepository->saveMultiple($inventoryItems);

        // When
        $listedInventoryItems = $this->inventoryItemRepository->list($listOptions);

        // Then
        self::assertEquals($page, $listedInventoryItems->currentPage());
        self::assertEquals($itemsPerPage, $listedInventoryItems->perPage());

        // collect(items)->toArray() needed to use the toArray() method on InventoryItem,
        // otherwise it will return an array of InventoryItems
        self::assertEquals($inventoryItems->slice(0, 10)->values()->toArray(), collect($listedInventoryItems->items())->toArray());
    }

    /** @test */
    // TODO: Task 19012: Gebruik property based testing om meerdere input van de list() functie te testen
    public function it_should_list_inventory_items_with_specified_items_by_page_and_page_3()
    {
        // Given
        $itemsPerPage = 50;
        $page = 1;
        $listOptions = [];
        $listOptions['pagingOptions']['itemsPerPage'] = $itemsPerPage;
        $listOptions['pagingOptions']['page'] = $page;

        $inventoryItems = InventoryItemFactory::createMultiple(7, [
            'stock' => new Stock(1, 0, 0)
        ]);
        $this->inventoryItemRepository->saveMultiple($inventoryItems);

        // When
        $listedInventoryItems = $this->inventoryItemRepository->list($listOptions);

        // Then
        self::assertEquals($page, $listedInventoryItems->currentPage());
        self::assertEquals($itemsPerPage, $listedInventoryItems->perPage());

        // collect(items)->toArray() needed to use the toArray() method on InventoryItem,
        // otherwise it will return an array of InventoryItems
        self::assertEquals($inventoryItems->values()->toArray(), collect($listedInventoryItems->items())->toArray());
    }

    // TODO: Task 18994: Schrijf tests voor EloquentInventoryItemRepository die de list() functie test (sortBy, sortDesc, search, filters)
    /** @test */
    public function it_should_find_all_inventory_items()
    {
        // Given
        $inventoryItems = InventoryItemFactory::createMultiple(10, [
            'stock' => new Stock(1, 0, 0)
        ]);

        // When
        $this->inventoryItemRepository->saveMultiple($inventoryItems);

        // Then
        $foundInventoryItems = $this->inventoryItemRepository->findAll();
        self::assertEquals($inventoryItems->toArray(), $foundInventoryItems->toArray());
    }

    /** @test */
    public function it_should_chunk_inventory_items()
    {
        // Given
        $inventoryItems = InventoryItemFactory::createMultiple(10, [
            'stock' => new Stock(1, 0, 0)
        ]);

        // When
        $this->inventoryItemRepository->saveMultiple($inventoryItems);

        // Then
        $totalChunks = 5;
        $chunkCounter = 0;
        $this->inventoryItemRepository->chunk('products', 2, static function($item) use (&$chunkCounter) {
            $chunkCounter++;
        });

        self::assertEquals($totalChunks, $chunkCounter);
    }

    /** @test */
    public function it_should_find_all_inventory_items_by_registration_numbers()
    {
        // Given
        $inventoryItems = InventoryItemFactory::createMultiple(10, [
            'stock' => new Stock(1, 0, 0)
        ]);
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
    public function it_should_add_one_inventory_item()
    {
        // Given
        $inventoryItem = InventoryItemFactory::create([
            'stock' => new Stock(1, 0, 0)
        ]);

        // When
        $addedInventoryItem = $this->inventoryItemRepository->save($inventoryItem);

        // Then
        self::assertEquals(1, $addedInventoryItem->registrationNumber());

        // Needed because otherwise they will never be equal
        $inventoryItem->changeRegistrationNumber(1);
        self::assertEquals($inventoryItem->toArray(), $addedInventoryItem->toArray());
    }

    /** @test */
    public function it_should_find_inventory_item_by_registration_number()
    {
        // Given
        $inventoryItems = InventoryItemFactory::createMultiple(10, [
            'stock' => new Stock(1, 0, 0)
        ]);
        $this->inventoryItemRepository->saveMultiple($inventoryItems);

        // When
        // TODO: Task 18940: Denk na over het gebruik van ID's uit de database op het domein object
        // Nu weet ik toevallig dat het 1, 2, ..., N is en dat ik de first() moet asserten
        $foundInventoryItem = $this->inventoryItemRepository->findOneByRegistrationNumber(1);

        // Then
        self::assertNotNull($foundInventoryItem);
        self::assertEquals($inventoryItems->first()->toArray(), $foundInventoryItem->toArray());
    }

    /** @test */
    public function it_should_find_inventory_items_by_registration_numbers()
    {
        // Given
        $inventoryItems = InventoryItemFactory::createMultiple(10, [
            'stock' => new Stock(1, 0, 0)
        ]);
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
        $inventoryItems = InventoryItemFactory::createMultiple(10, [
            'stock' => new Stock(1, 0, 0)
        ]);
        $this->inventoryItemRepository->saveMultiple($inventoryItems);

        // When
        $updatedInventoryItem = InventoryItemFactory::create([
            'registrationNumber' => $inventoryItems->first()->registrationNumber(),
            'productCode' => $inventoryItems->first()->productCode()
        ]);

        $this->inventoryItemRepository->update($updatedInventoryItem);

        // Then
        $foundInventoryItem = $this->inventoryItemRepository->findOneByProductCode($updatedInventoryItem->productCode());
        $foundInventoryItemArray = $foundInventoryItem->toArray();
        $expectedInventoryItemArray = $updatedInventoryItem->toArray();
        unset($expectedInventoryItemArray['stock']['desired']);
        unset($expectedInventoryItemArray['stock']['current']);
        unset($expectedInventoryItemArray['stock']['reserved']);
        unset($expectedInventoryItemArray['purchase_settings']);
        unset($expectedInventoryItemArray['supplier_id']);

        unset($foundInventoryItemArray['stock']['desired']);
        unset($foundInventoryItemArray['stock']['current']);
        unset($foundInventoryItemArray['stock']['reserved']);
        unset($foundInventoryItemArray['purchase_settings']);
        unset($foundInventoryItemArray['supplier_id']);
        self::assertEquals($expectedInventoryItemArray, $foundInventoryItemArray);
    }

    /** @test */
    public function it_should_remove_inventory_items_by_ids()
    {
        // Given
        $inventoryItems = InventoryItemFactory::createMultiple(10, [
            'stock' => new Stock(1, 0, 0)
        ]);
        $this->inventoryItemRepository->saveMultiple($inventoryItems);

        // When
        // TODO: Task 18940: Denk na over het gebruik van ID's uit de database op het domein object
         $this->inventoryItemRepository->removeByRegistrationNumbers([
            1, 2, 3
        ]);

        // Then
        $foundInventoryItems = $this->inventoryItemRepository->findAll();

        // Remove first 3 items from initial collection
        $inventoryItems->shift(3);
        self::assertEquals(7, $foundInventoryItems->count());
        self::assertEquals($inventoryItems->toArray(), $foundInventoryItems->toArray());
    }
}
