<?php


namespace App\Inventory\Application\GeneratePurchaseRecommendations;

use App\Inventory\Domain\Exceptions\SupplierNotFoundException;
use App\Inventory\Domain\InventoryItems\InventoryItem;
use App\Inventory\Domain\PurchaseRecommendations\PurchaseRecommendation;
use App\Inventory\Domain\Repositories\InventoryItemRepositoryInterface;
use App\Inventory\Domain\Repositories\SupplierRepositoryInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class GeneratePurchaseRecommendations implements GeneratePurchaseRecommendationsInterface
{
    protected InventoryItemRepositoryInterface $inventoryItemRepository;
    protected SupplierRepositoryInterface $supplierRepository;
    protected Collection $purchaseOrderRecommendations;

    /**
     * GeneratePurchasingAdvice constructor.
     */
    public function __construct(InventoryItemRepositoryInterface $inventoryItemRepository,
                                SupplierRepositoryInterface $supplierRepository)
    {
        $this->inventoryItemRepository = $inventoryItemRepository;
        $this->supplierRepository = $supplierRepository;
    }

    /**
     * @inheritDoc
     * @throws SupplierNotFoundException
     */
    public function execute(GeneratePurchaseRecommendationsInput $input): GeneratePurchaseRecommendationsResult
    {
        $suppliers = collect($input->suppliers());

        $this->purchaseOrderRecommendations = collect();

        $suppliers->each(function (array $supplier) {
            $tags = collect(Arr::get($supplier, 'tags'));
            $supplierId = Arr::get($supplier, 'id');

            $supplier = $this->supplierRepository->findOneById($supplierId);

            if ($supplier === null)
            {
                throw new SupplierNotFoundException('Supplier with id ' . $supplierId . ' not found');
            }

            $tags->each(function (string $filterByTag) use ($supplier) {
                $filterBySupplierId = $supplier->identity();

                $inventoryItems = $this->fetchAndFilterInventoryItems($filterBySupplierId, $filterByTag);

                $generatedPurchaseRecommendations = $this->generatePurchaseRecommendations($inventoryItems, $supplier->identity(), $filterByTag);

                if ($generatedPurchaseRecommendations->isNotEmpty())
                {
                    $this->purchaseOrderRecommendations = $this->purchaseOrderRecommendations->merge($generatedPurchaseRecommendations);
                }
            });
        });

        return new GeneratePurchaseRecommendationsResult($this->purchaseOrderRecommendations);
    }

    private function fetchAndFilterInventoryItems(int $filterBySupplierId, string $filterByTag): Collection
    {
        $inventoryItems = $this->inventoryItemRepository->findAllByTagAndSupplierId($filterByTag, $filterBySupplierId);

        return $inventoryItems->filter->needsStockReplenishment();
    }

    private function generatePurchaseRecommendations(Collection $inventoryItems, string $supplierId, string $tag): Collection
    {
        return $inventoryItems->map(function (InventoryItem $inventoryItem) use ($supplierId, $tag) {
            return new PurchaseRecommendation($inventoryItem->stock(), $inventoryItem->purchaseSettings(), $inventoryItem->productCode(), $inventoryItem->externalProductCode(), $supplierId, $tag);
        })->filter(function (PurchaseRecommendation $purchaseRecommendation) {
            return $purchaseRecommendation->recommend() !== 0;
        });
    }
}
