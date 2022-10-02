<?php

namespace App\Inventory\Infrastructure\Jobs;

use App\Inventory\Application\GeneratePurchaseRecommendations\GeneratePurchaseRecommendations;
use App\Inventory\Application\GeneratePurchaseRecommendations\GeneratePurchaseRecommendationsInput;
use App\Inventory\Application\MailPurchaseOrderToSupplier\MailPurchaseOrderToSupplier;
use App\Inventory\Application\MailPurchaseOrderToSupplier\MailPurchaseOrderToSupplierInput;
use App\Inventory\Domain\Exceptions\SupplierNotFoundException;
use App\Inventory\Domain\Repositories\InventoryItemRepositoryInterface;
use App\Inventory\Domain\Repositories\PurchaseOrderRepositoryInterface;
use App\Inventory\Domain\Repositories\SupplierRepositoryInterface;
use App\Inventory\Domain\Services\MailerServiceInterface;
use App\Inventory\Domain\Services\ProductCatalogServiceInterface;
use App\Inventory\Domain\Services\PurchaseOrderMailManagerInterface;
use App\Inventory\Domain\Services\PurchaseOrderTemplateManagerInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateAndMailPurchaseOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected InventoryItemRepositoryInterface $inventoryItemRepository;
    protected SupplierRepositoryInterface $supplierRepository;
    protected string $tag;
    protected string $supplierId;
    protected MailerServiceInterface $mailerService;
    protected ProductCatalogServiceInterface $productCatalogService;
    protected PurchaseOrderTemplateManagerInterface $purchaseOrderTemplateManager;
    protected PurchaseOrderRepositoryInterface $purchaseOrderRepository;
    protected PurchaseOrderMailManagerInterface $purchaseOrderMailManager;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(InventoryItemRepositoryInterface $inventoryItemRepository,
                                SupplierRepositoryInterface $supplierRepository,
                                MailerServiceInterface $mailerService,
                                ProductCatalogServiceInterface $productCatalogService,
                                PurchaseOrderTemplateManagerInterface $purchaseOrderTemplateManager,
                                PurchaseOrderMailManagerInterface $purchaseOrderMailManager,
                                PurchaseOrderRepositoryInterface $purchaseOrderRepository,
                                string $supplierId,
                                string $tag)
    {
        $this->inventoryItemRepository = $inventoryItemRepository;
        $this->supplierRepository = $supplierRepository;
        $this->mailerService = $mailerService;
        $this->productCatalogService = $productCatalogService;
        $this->purchaseOrderTemplateManager = $purchaseOrderTemplateManager;
        $this->purchaseOrderMailManager = $purchaseOrderMailManager;
        $this->purchaseOrderRepository = $purchaseOrderRepository;
        $this->supplierId = $supplierId;
        $this->tag = $tag;
    }


    /**
     * Execute the job.
     *
     * @return void
     * @throws SupplierNotFoundException
     */
    public function handle()
    {
        // Draai inkoop advies
        $generatePurchaseRecommendations = new GeneratePurchaseRecommendations($this->inventoryItemRepository, $this->supplierRepository);
        $generatePurchaseRecommendationsInput = new GeneratePurchaseRecommendationsInput([
            'suppliers' => [
                0 => [
                    'id' => $this->supplierId,
                    'tags' => [
                        0 => $this->tag
                    ]
                ]
            ]
        ]);

        $generatePurchaseRecommendationsResult = $generatePurchaseRecommendations->execute($generatePurchaseRecommendationsInput);

        if ($generatePurchaseRecommendationsResult->purchasingRecommendations()->isNotEmpty())
        {
            // Mail inkoop order
            $mailPurchaseOrderToSupplier = new MailPurchaseOrderToSupplier($this->mailerService, $this->inventoryItemRepository, $this->supplierRepository,
                $this->productCatalogService, $this->purchaseOrderTemplateManager, $this->purchaseOrderMailManager, $this->purchaseOrderRepository);
            $mailPurchaseOrderToSupplierInput = new MailPurchaseOrderToSupplierInput([
                'included_data' => [
                    'product_code' => 'Artikelnummer',
                    'product_code_supplier' => 'Artikelnummer leverancier',
                    'product_name' => 'Artikelnaam',
                    'quantity' => 'Aantal',
                    'delivery_work_days' => 'Levertijd in werkdagen'
                ],
                'purchase_recommendations' => $generatePurchaseRecommendationsResult->purchasingRecommendations()->toArray()
            ]);

            $mailPurchaseOrderToSupplierResult = $mailPurchaseOrderToSupplier->execute($mailPurchaseOrderToSupplierInput);
        }
    }

}
