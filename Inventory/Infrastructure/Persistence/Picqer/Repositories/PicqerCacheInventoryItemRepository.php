<?php


namespace App\Inventory\Infrastructure\Persistence\Picqer\Repositories;


use App\Inventory\Domain\Exceptions\InventoryItemRepositoryOperationException;
use App\Inventory\Domain\InventoryItems\InventoryItem;
use App\Inventory\Domain\InventoryItems\InventoryItemAnalytics;
use App\Inventory\Domain\Repositories\DestinationInventoryItemRepositoryInterface;
use App\Inventory\Domain\Repositories\SourceInventoryItemRepositoryInterface;
use App\Inventory\Infrastructure\Exceptions\PicqerInventoryItemRepositoryException;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Picqer\Api\Client;

class PicqerCacheInventoryItemRepository implements SourceInventoryItemRepositoryInterface, DestinationInventoryItemRepositoryInterface
{
    private Client $apiClient;
    protected PicqerInventoryItemRepository $picqerInventoryItemRepository;

    public function __construct(PicqerInventoryItemRepository $picqerInventoryItemRepository)
    {
        $this->picqerInventoryItemRepository = $picqerInventoryItemRepository;
    }

    /**
     * @inheritDoc
     */
    public function list(array $listOptions = []): LengthAwarePaginator
    {
        // TODO: Implement list() method.
    }

    /**
     * @inheritDoc
     * @throws InventoryItemRepositoryOperationException
     */
    public function save(InventoryItem $inventoryItem): InventoryItem
    {
        return $this->picqerInventoryItemRepository->save($inventoryItem);
    }

    /**
     * @inheritDoc
     */
    public function findOneByRegistrationNumber($registrationNumber): ?InventoryItem
    {
        // TODO: Implement find() method.
    }

    /**
     * @inheritDoc
     */
    public function findAllByRegistrationNumbers(array $registrationNumbers): Collection
    {
        // TODO: Implement getByIds() method.
    }

    /**
     * @inheritDoc
     */
    public function removeByRegistrationNumbers(array $registrationNumbers): void
    {
        // TODO: Implement removeByIds() method.
    }

    public function saveMultiple(Collection $inventoryItems): void
    {
        $this->picqerInventoryItemRepository->saveMultiple($inventoryItems);
    }

    /**
     * @throws PicqerInventoryItemRepositoryException
     */
    public function findAll(bool $withDesiredStock = true): Collection
    {
        return $this->picqerInventoryItemRepository->findAll($withDesiredStock);
    }

    /**
     * @throws PicqerInventoryItemRepositoryException
     */
    public function update(InventoryItem $inventoryItem): void
    {
        $this->picqerInventoryItemRepository->update($inventoryItem);
    }

    /**
     * @throws InventoryItemRepositoryOperationException
     */
    public function findOneByProductCode(string $productCode): ?InventoryItem
    {
        return $this->picqerInventoryItemRepository->findOneByProductCode($productCode);
    }

    public function delete(InventoryItem $inventoryItem): void
    {
        // TODO: Implement delete() method.
    }

    /**
     * @throws PicqerInventoryItemRepositoryException
     */
    public function findAllByType(string $type): Collection
    {
        return $this->picqerInventoryItemRepository->findAllByType($type);
    }

    public function findAllByProductCode(string $productCode): Collection
    {
        return collect([$this->findOneByProductCode($productCode)]);
    }

    public function getAnalytics(CarbonImmutable $startRangeDate = null, CarbonImmutable $endRangeDate = null, string $type = null): ?InventoryItemAnalytics
    {
        // TODO: Implement getStatistics() method.
    }

    /**
     * @throws PicqerInventoryItemRepositoryException
     */
    public function findAllByTagAndSupplierId(string $tag, int $supplierId): Collection
    {
        $cacheKey = sprintf('inventory_items.tag.%s.supplier_id.%s', $tag, $supplierId);

        if (Cache::has($cacheKey))
        {
            return Cache::get($cacheKey);
        }

        $inventoryItems = $this->picqerInventoryItemRepository->findAllByTagAndSupplierId($tag, $supplierId);
        Cache::put($cacheKey, $inventoryItems);
        return $inventoryItems;
    }
}
