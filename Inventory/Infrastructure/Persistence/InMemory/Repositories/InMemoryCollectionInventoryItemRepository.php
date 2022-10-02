<?php


namespace App\Inventory\Infrastructure\Persistence\InMemory\Repositories;


use App\Inventory\Domain\InventoryItems\InventoryItem;
use App\Inventory\Domain\InventoryItems\InventoryItemAnalytics;
use App\Inventory\Domain\Repositories\DestinationInventoryItemRepositoryInterface;
use App\Inventory\Domain\Repositories\SourceInventoryItemRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class InMemoryCollectionInventoryItemRepository implements SourceInventoryItemRepositoryInterface, DestinationInventoryItemRepositoryInterface
{
    private Collection $inventoryItems;

    public function __construct()
    {
        $this->inventoryItems = collect();
    }

    public function list(array $listOptions = []): LengthAwarePaginator
    {
        // TODO: Task 18995: Schrijf de list functie voor InMemoryCollectionInventoryItemRepository en test deze af
        return new LengthAwarePaginator($this->inventoryItems, $this->inventoryItems->count(), 10);
    }

    public function save(InventoryItem $inventoryItem): InventoryItem
    {
        $this->inventoryItems->push($inventoryItem);
        return $inventoryItem;
    }

    public function saveMultiple(Collection $inventoryItems): void
    {
        $this->inventoryItems = $this->inventoryItems->merge($inventoryItems);
    }

    public function findOneByRegistrationNumber(int|string $registrationNumber): ?InventoryItem
    {
        return $this->inventoryItems->first(function (InventoryItem $inventoryItem) use ($registrationNumber) {
            return $registrationNumber === $inventoryItem->registrationNumber();
        });
    }

    public function findAllByRegistrationNumbers(array $registrationNumbers): Collection
    {
        return $this->inventoryItems->filter(function (InventoryItem $inventoryItem) use ($registrationNumbers) {
            return (in_array($inventoryItem->registrationNumber(), $registrationNumbers));
        });
    }

    public function findAll(bool $withDesiredStock = true): Collection
    {
        return $this->inventoryItems;
    }

    public function removeByRegistrationNumbers(array $registrationNumbers): void
    {
        $this->inventoryItems = $this->inventoryItems->filter(function (InventoryItem $inventoryItem) use ($registrationNumbers){
            return ! in_array($inventoryItem->registrationNumber(), $registrationNumbers);
        })->values();
    }

    public function update(InventoryItem $inventoryItem): void
    {
        $this->inventoryItems = $this->inventoryItems->map(function (InventoryItem $existingInventoryItem) use ($inventoryItem)  {
            if ($existingInventoryItem->registrationNumber() === $inventoryItem->registrationNumber())
            {
                return $inventoryItem;
            }

            return $existingInventoryItem;
        });
    }

    public function findOneByProductCode(string $productCode): ?InventoryItem
    {
        return $this->inventoryItems->first(function (InventoryItem $inventoryItem) use ($productCode) {
            return $productCode === $inventoryItem->productCode();
        });
    }

    public function delete(InventoryItem $inventoryItem): void
    {
        $this->inventoryItems = $this->inventoryItems->map(function (InventoryItem $existingInventoryItem) use ($inventoryItem) {
            if ($existingInventoryItem->productCode() === $inventoryItem->productCode())
            {
                return $inventoryItem;
            }

            return $existingInventoryItem;
        });
    }

    /**
     * @param string $entity
     * @param int $size
     * @param callable|null $callback
     * @return void
     */
    public function chunk(string $entity, int $size = 100, callable $callback = null): void
    {
        // TODO: Implement chunk() method.
    }

    public function findAllByType(string $type): Collection
    {
        return $this->inventoryItems->filter(function (InventoryItem $inventoryItem) use ($type) {
            return $inventoryItem->type() == $type;
        });
    }

    public function findAllByProductCode(string $productCode): Collection
    {
        return $this->inventoryItems->filter(function (InventoryItem $inventoryItem) use ($productCode) {
            return $inventoryItem->productCode() == $productCode;
        });
    }

    public function getAnalytics(CarbonImmutable $startRangeDate = null, CarbonImmutable $endRangeDate = null, string $type = null): ?InventoryItemAnalytics
    {
        return $this->inventoryItems->when($type, function(Collection $collection) use ($type) {
            $collection->filter(function(InventoryItem $inventoryItem) use ($type) {
                return $inventoryItem->type() === $type;
            });
        })->when($startRangeDate, function(Collection $collection) use ($startRangeDate) {
            $collection->filter(function(InventoryItem $inventoryItem) use ($startRangeDate) {
                return $inventoryItem->createdAt()->gte($startRangeDate);
            });
        })->when($endRangeDate, function(Collection $collection) use ($endRangeDate) {
            $collection->filter(function(InventoryItem $inventoryItem) use ($endRangeDate) {
                return $inventoryItem->createdAt()->lte($endRangeDate);
            });
        });
    }

    public function findAllByTagAndSupplierId(string $tag, int $supplierId): Collection
    {
        return $this->inventoryItems->filter(function (InventoryItem $inventoryItem) use ($tag, $supplierId) {
            return $inventoryItem->tags()->contains($tag) && $inventoryItem->supplierId() == $supplierId;
        });
    }

    public function findAllAvailableByProductCode(string $productCode): Collection
    {
        return $this->inventoryItems->filter(function (InventoryItem $inventoryItem) use ($productCode) {
            return $productCode === $inventoryItem->productCode() && !$inventoryItem->sold();
        });
    }
}
