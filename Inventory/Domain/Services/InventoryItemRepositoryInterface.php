<?php


namespace App\Inventory\Domain\Repositories;


use App\Inventory\Domain\InventoryItems\InventoryItem;
use App\Inventory\Domain\InventoryItems\InventoryItemAnalytics;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface InventoryItemRepositoryInterface
{
    /**
     * @param array $listOptions
     * @return LengthAwarePaginator
     */
    public function list(array $listOptions = []): LengthAwarePaginator;

    /**
     * @param InventoryItem $inventoryItem
     * @return InventoryItem
     */
    public function save(InventoryItem $inventoryItem): InventoryItem;

    /**
     * @param Collection $inventoryItems
     * @return void
     */
    public function saveMultiple(Collection $inventoryItems): void;

    /**
     * @param int|string $registrationNumber
     * @return InventoryItem|null
     */
    public function findOneByRegistrationNumber(string|int $registrationNumber): ?InventoryItem;

    /**
     * @param int[] $registrationNumbers
     * @return Collection
     */
    public function findAllByRegistrationNumbers(array $registrationNumbers): Collection;

    /**
     * @param bool $withDesiredStock
     * @return Collection
     */
    public function findAll(bool $withDesiredStock = true): Collection;

    /**
     * @param string $tag
     * @return Collection
     */
    public function findAllByTagAndSupplierId(string $tag, int $supplierId): Collection;

    /**
     * @param string $type
     * @return Collection
     */
    public function findAllByType(string $type): Collection;

    /**
     * @param array $registrationNumbers
     * @return void
     */
    public function removeByRegistrationNumbers(array $registrationNumbers): void;

    /**
     * @param InventoryItem $inventoryItem
     * @return void
     */
    public function update(InventoryItem $inventoryItem): void;

    /**
     * @param string $productCode
     * @return InventoryItem|null
     */
    public function findOneByProductCode(string $productCode): ?InventoryItem;

    /**
     * @param string $productCode
     * @return Collection
     */
    public function findAllByProductCode(string $productCode): Collection;

    /**
     * @param string $productCode
     * @return Collection
     */
    public function findAllAvailableByProductCode(string $productCode): Collection;

    /**
     * @param InventoryItem $inventoryItem
     * @return void
     */
    public function delete(InventoryItem $inventoryItem): void;

    /**
     * @param CarbonImmutable|null $startRangeDate
     * @param CarbonImmutable|null $endRangeDate
     * @param string|null $type
     * @return InventoryItemAnalytics|null
     */
    public function getAnalytics(CarbonImmutable $startRangeDate = null, CarbonImmutable $endRangeDate = null, string $type = null): ?InventoryItemAnalytics;

}
