<?php


namespace App\Inventory\Application\GeneratePurchaseOrder;

use App\Inventory\Domain\Exceptions\GeneratePurchaseOrderException;
use App\Inventory\Domain\Exceptions\InventoryItemNotFoundException;
use App\Inventory\Domain\PurchaseOrders\CreatePurchaseOrder;
use App\Inventory\Domain\PurchaseOrders\CreatePurchaseOrderTemplateSettings;
use App\Inventory\Domain\PurchaseOrders\PurchaseOrderLine;
use App\Inventory\Domain\Repositories\InventoryItemRepositoryInterface;
use App\Inventory\Domain\Repositories\PurchaseOrderRepositoryInterface;
use App\Inventory\Domain\Repositories\SupplierRepositoryInterface;
use App\Inventory\Domain\Services\ProductCatalogServiceInterface;
use App\Inventory\Domain\Services\PurchaseOrderTemplateManagerInterface;
use App\Inventory\Domain\Suppliers\Supplier;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class GeneratePurchaseOrder implements GeneratePurchaseOrderInterface
{
    protected InventoryItemRepositoryInterface $inventoryItemRepository;
    protected SupplierRepositoryInterface $supplierRepository;
    protected ProductCatalogServiceInterface $productCatalogService;
    protected PurchaseOrderTemplateManagerInterface $purchaseOrderTemplateManager;
    protected PurchaseOrderRepositoryInterface $purchaseOrderRepository;

    /**
     * GeneratePurchaseOrder constructor.
     */
    public function __construct(InventoryItemRepositoryInterface $inventoryItemRepository,
                                SupplierRepositoryInterface $supplierRepository,
                                ProductCatalogServiceInterface $productCatalogService,
                                PurchaseOrderTemplateManagerInterface $purchaseOrderTemplateManager,
                                PurchaseOrderRepositoryInterface $purchaseOrderRepository)
    {
        $this->inventoryItemRepository = $inventoryItemRepository;
        $this->supplierRepository = $supplierRepository;
        $this->productCatalogService = $productCatalogService;
        $this->purchaseOrderTemplateManager = $purchaseOrderTemplateManager;
        $this->purchaseOrderRepository = $purchaseOrderRepository;
    }

    /**
     * @inheritDoc
     * @throws GeneratePurchaseOrderException|InventoryItemNotFoundException
     */
    public function execute(GeneratePurchaseOrderInput $input): GeneratePurchaseOrderResult
    {
        $purchaseOrderLines = collect($input->purchaseRecommendations());

        if ($purchaseOrderLines->isEmpty())
        {
            throw new GeneratePurchaseOrderException('Purchase order lines are empty');
        }

        if (! $this->isSingleSupplierAndTag($purchaseOrderLines))
        {
            throw new GeneratePurchaseOrderException('The supplier and tag should be the same for all re');
        }

        $supplier = $this->getSupplier($purchaseOrderLines);
        $tag = $this->getTag($purchaseOrderLines);

        $purchaseOrder = CreatePurchaseOrder::concept([
            'purchase_order_lines' => collect(),
            'supplier_id' => $supplier->identity(),
            'remarks' => 'Aangemaakt door Wall-E'
        ]);

        $purchaseOrderLines->map(function (array $purchaseRecommendation) use ($purchaseOrder) {
            $productCode = Arr::get($purchaseRecommendation, 'product_code');
            $quantity = Arr::get($purchaseRecommendation, 'amount_to_be_purchased');
            $productCatalogProduct = $this->productCatalogService->getProduct($productCode);
            $purchaseOrderLine = new PurchaseOrderLine($productCode, $productCatalogProduct->productCodeSupplier(), $productCatalogProduct->name(), $quantity, $productCatalogProduct->price(), $productCatalogProduct->deliveryWorkDays());
            $purchaseOrder->addPurchaseOrderLine($purchaseOrderLine);
        });

        $purchaseOrder = $this->purchaseOrderRepository->save($purchaseOrder);
        $purchaseOrderTemplate = $this->purchaseOrderTemplateManager->determineTemplate($purchaseOrder, $supplier, $tag);
        $includedData = $input->includedData();
        $purchaseOrderTemplateSettings = CreatePurchaseOrderTemplateSettings::fromArray($includedData);

        return new GeneratePurchaseOrderResult($purchaseOrderTemplate->export($purchaseOrderTemplateSettings), $purchaseOrder);
    }

    /**
     * @throws InventoryItemNotFoundException
     */
    private function getSupplier(Collection $purchaseOrderLines): Supplier
    {
        $productCode = Arr::get($purchaseOrderLines->first(), 'product_code');
        $inventoryItem = $this->inventoryItemRepository->findOneByProductCode($productCode);

        if ($inventoryItem === null)
        {
            throw new InventoryItemNotFoundException('Inventory item with product code ' . $productCode . ' not found');
        }

        return $this->supplierRepository->findOneById($inventoryItem->supplierId());
    }

    private function getTag(Collection $purchaseOrderLines): string
    {
        return Arr::get($purchaseOrderLines[0], 'tag');
    }

    private function isSingleSupplierAndTag(Collection $purchaseOrderLines): bool
    {
        $tag = Arr::get($purchaseOrderLines->first(), 'tag');
        $supplierId = Arr::get($purchaseOrderLines->first(), 'supplier_id');

        foreach ($purchaseOrderLines as $purchaseOrderLine)
        {
            if (Arr::get($purchaseOrderLine, 'tag') != $tag || Arr::get($purchaseOrderLine, 'supplier_id') != $supplierId)
            {
                return false;
            }
        }

        return true;
    }
}
