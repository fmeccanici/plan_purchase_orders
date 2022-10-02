<?php

namespace Tests\Feature\Inventory\GeneratePurchaseOrder;

use App\Inventory\Domain\InventoryItems\InventoryItemFactory;
use App\Inventory\Domain\PurchaseOrders\CreatePurchaseOrder;
use App\Inventory\Domain\PurchaseOrders\PurchaseOrderLine;
use App\Inventory\Domain\PurchaseRecommendations\CreatePurchaseOrderRecommendation;
use App\Inventory\Domain\PurchaseRecommendations\PurchaseRecommendation;
use App\Inventory\Domain\Repositories\InventoryItemRepositoryInterface;
use App\Inventory\Domain\Repositories\PurchaseOrderRepositoryInterface;
use App\Inventory\Domain\Repositories\SupplierRepositoryInterface;
use App\Inventory\Domain\Services\MailerServiceInterface;
use App\Inventory\Domain\Services\ProductCatalogProduct;
use App\Inventory\Domain\Services\ProductCatalogServiceInterface;
use App\Inventory\Domain\Suppliers\CreateSupplier;
use App\Inventory\Infrastructure\Persistence\InMemory\Repositories\InMemoryCollectionInventoryItemRepository;
use App\Inventory\Infrastructure\Persistence\InMemory\Repositories\InMemoryCollectionPurchaseOrderRepository;
use App\Inventory\Infrastructure\Persistence\InMemory\Repositories\InMemoryCollectionSupplierRepository;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\Feature\Inventory\MailPurchaseOrderToSupplier\TestMailerService;
use Tests\TestCase;

class GeneratePurchaseOrderTest extends TestCase
{
    protected InMemoryCollectionInventoryItemRepository $inventoryItemRepository;
    protected InMemoryCollectionSupplierRepository $supplierRepository;
    protected ProductCatalogServiceInterface $productCatalogService;
    protected TestMailerService $mailerService;
    protected InMemoryCollectionPurchaseOrderRepository $purchaseOrderRepository;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->supplierRepository = new InMemoryCollectionSupplierRepository();
        $this->app->bind(SupplierRepositoryInterface::class, function () {return $this->supplierRepository;});

        $this->mailerService = new TestMailerService();
        $this->app->bind(MailerServiceInterface::class, function () {return $this->mailerService;});

        $this->purchaseOrderRepository = new InMemoryCollectionPurchaseOrderRepository();
        $this->app->bind(PurchaseOrderRepositoryInterface::class, function () {return $this->purchaseOrderRepository;});

    }

    /** @test */
    public function it_should_generate_purchase_order_for_peitsman()
    {
        // Given
        Storage::fake();

        $supplier = CreateSupplier::peitsman();
        $supplier->setIdentity(1);

        $supplierRepositoryMock = $this->mock(SupplierRepositoryInterface::class, function (MockInterface $mock) use ($supplier) {
            $mock->shouldReceive('findOneById')
                ->once()
                ->andReturn($supplier);
        });
        $this->app->bind(SupplierRepositoryInterface::class, function () use ($supplierRepositoryMock) {return $supplierRepositoryMock;});

        $inventoryItemRepositoryMock = $this->mock(InventoryItemRepositoryInterface::class, function (MockInterface $mock) use ($supplier) {
            $mock->shouldReceive('findOneByProductCode')
                ->once()
                ->andReturn(InventoryItemFactory::create([
                    'supplierId' => $supplier->identity()
                ]));
        });

        $productCodeSupplier = 'Test Product Code Supplier';
        $productName = 'Test Product Name';
        $price = 10.0;
        $deliveryWorkDays = 2;

        $productCatalogServiceMock = $this->mock(ProductCatalogServiceInterface::class, function (MockInterface $mock) use ($productCodeSupplier, $productName, $price, $deliveryWorkDays) {
            $mock->shouldReceive('getProduct')
                ->andReturnUsing(function ($productCode) use ($productCodeSupplier, $productName, $price, $deliveryWorkDays) {
                    return new ProductCatalogProduct($productCode, $productCodeSupplier, $productName, $price, $deliveryWorkDays);
                });
        });

        $this->app->bind(ProductCatalogServiceInterface::class, function () use ($productCatalogServiceMock) {return $productCatalogServiceMock;});

        $this->app->bind(InventoryItemRepositoryInterface::class, function () use ($inventoryItemRepositoryMock) {return $inventoryItemRepositoryMock;});

        $url = route('generate-purchase-order');
        $purchaseRecommendations = CreatePurchaseOrderRecommendation::multiple(5);
        $includedData = [
                'product_code' => 'Artikelnaam',
                'product_code_supplier' => 'Artikelnummer leverancier',
                'product_name' => 'Artikelnaam',
                'quantity' => 'Aantal'
            ];

        // When
        $response = $this->post($url, [
            'included_data' => $includedData,
            'purchase_recommendations' => $purchaseRecommendations->toArray()
        ]);

        // Then
        $purchaseOrderLines = $purchaseRecommendations->map(function (PurchaseRecommendation $purchaseRecommendation) use ($productCodeSupplier, $productName, $price, $deliveryWorkDays) {
            return new PurchaseOrderLine($purchaseRecommendation->productCode(), $productCodeSupplier, $productName, $purchaseRecommendation->recommend(), $price, $deliveryWorkDays);
        });

        $purchaseOrder = CreatePurchaseOrder::concept([
            'purchase_order_lines' => $purchaseOrderLines,
            'supplier_id' => $supplier->identity(),
            'remarks' => 'Aangemaakt door Wall-E'
        ]);

        $purchaseOrder->setIdentity($this->purchaseOrderRepository->currentId());
        $purchaseOrder->setReference($this->purchaseOrderRepository->currentReference());

        self::assertCount(1, Storage::allFiles('purchase_orders/'));
        $response->assertJson([
            'payload' => [
                'purchase_order' => $purchaseOrder->toArray()
            ]
        ]);
    }
}