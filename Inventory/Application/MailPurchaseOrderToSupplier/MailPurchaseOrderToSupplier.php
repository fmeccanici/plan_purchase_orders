<?php


namespace App\Inventory\Application\MailPurchaseOrderToSupplier;

use App\Inventory\Application\GeneratePurchaseOrder\GeneratePurchaseOrder;
use App\Inventory\Application\GeneratePurchaseOrder\GeneratePurchaseOrderInput;
use App\Inventory\Domain\Exceptions\SupplierNotFoundException;
use App\Inventory\Domain\Mails\NotificationMinimumPurchaseAmountNotReached;
use App\Inventory\Domain\Mails\NotificationMinimumPurchaseAmountNotReachedMail;
use App\Inventory\Domain\PurchaseOrders\PurchaseOrderStatus;
use App\Inventory\Domain\Repositories\InventoryItemRepositoryInterface;
use App\Inventory\Domain\Repositories\PurchaseOrderRepositoryInterface;
use App\Inventory\Domain\Repositories\SupplierRepositoryInterface;
use App\Inventory\Domain\Services\MailerServiceInterface;
use App\Inventory\Domain\Services\ProductCatalogServiceInterface;
use App\Inventory\Domain\Services\PurchaseOrderMailManagerInterface;
use App\Inventory\Domain\Services\PurchaseOrderTemplateManagerInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class MailPurchaseOrderToSupplier implements MailPurchaseOrderToSupplierInterface
{
    protected SupplierRepositoryInterface $supplierRepository;
    protected MailerServiceInterface $mailerService;
    protected ProductCatalogServiceInterface $productCatalogService;
    protected InventoryItemRepositoryInterface $inventoryItemRepository;
    protected PurchaseOrderRepositoryInterface $purchaseOrderRepository;
    protected PurchaseOrderTemplateManagerInterface $purchaseOrderTemplateManager;
    protected PurchaseOrderMailManagerInterface $purchaseOrderMailManager;

    /**
     * MailPurchaseOrderToSupplier constructor.
     */
    public function __construct(MailerServiceInterface $mailerService,
                                InventoryItemRepositoryInterface $inventoryItemRepository,
                                SupplierRepositoryInterface $supplierRepository,
                                ProductCatalogServiceInterface $productCatalogService,
                                PurchaseOrderTemplateManagerInterface $purchaseOrderTemplateManager,
                                PurchaseOrderMailManagerInterface $purchaseOrderMailManager,
                                PurchaseOrderRepositoryInterface $purchaseOrderRepository)
    {
        $this->mailerService = $mailerService;
        $this->supplierRepository = $supplierRepository;
        $this->inventoryItemRepository = $inventoryItemRepository;
        $this->productCatalogService = $productCatalogService;
        $this->purchaseOrderTemplateManager = $purchaseOrderTemplateManager;
        $this->purchaseOrderMailManager = $purchaseOrderMailManager;
        $this->purchaseOrderRepository = $purchaseOrderRepository;
    }

    /**
     * @inheritDoc
     * @throws SupplierNotFoundException
     */
    public function execute(MailPurchaseOrderToSupplierInput $input): MailPurchaseOrderToSupplierResult
    {
        $generatePurchaseOrder = new GeneratePurchaseOrder($this->inventoryItemRepository, $this->supplierRepository,
                                                            $this->productCatalogService, $this->purchaseOrderTemplateManager, $this->purchaseOrderRepository);

        $purchaseRecommendations = $input->purchaseRecommendations();

        $generatePurchaseOrderInput = new GeneratePurchaseOrderInput([
            'included_data' => $input->includedData(),
            'purchase_recommendations' => $input->purchaseRecommendations()
        ]);

        $generatePurchaseOrderResult = $generatePurchaseOrder->execute($generatePurchaseOrderInput);

        $supplierId = $this->getSupplierId(collect($purchaseRecommendations));

        $supplier = $this->supplierRepository->findOneById($supplierId);

        if ($supplier === null)
        {
            throw new SupplierNotFoundException('Supplier with id ' . $supplierId . ' not found');
        }

        $purchaseOrder = $generatePurchaseOrderResult->purchaseOrder();

        $minimumPurchaseAmount = $supplier->minimumPurchaseAmount();
        $totalAmount = $purchaseOrder->totalAmountInEuros();

        // Did not put this in the manager because I want to be explicit in the use case about this business rule
        if ($totalAmount < $minimumPurchaseAmount)
        {
            $mail = new NotificationMinimumPurchaseAmountNotReached($purchaseOrder);
            Mail::send($mail);
        } else {
            $tag = $this->getTag(collect($purchaseRecommendations));
            $mail = $this->purchaseOrderMailManager->determineMail($supplier, $tag, $generatePurchaseOrderResult->purchaseOrderExport()->url(), $generatePurchaseOrderResult->purchaseOrder());
            $mailSuccessfullySent = $this->mailerService->send($mail);

            if ($mailSuccessfullySent)
            {
                $purchaseOrder->changeStatus(PurchaseOrderStatus::PURCHASED);
                $this->purchaseOrderRepository->save($purchaseOrder);
            }
        }

        return new MailPurchaseOrderToSupplierResult();
    }

    private function getSupplierId(Collection $purchaseRecommendations): int
    {
        return Arr::get($purchaseRecommendations->first(), 'supplier_id');
    }

    private function getTag(Collection $purchaseRecommendations): string
    {
        return Arr::get($purchaseRecommendations->first(), 'tag');
    }
}
