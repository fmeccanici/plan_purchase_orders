<?php


namespace App\Inventory\Presentation\Http\Api;


use App\Inventory\Application\AddInventoryItem\AddInventoryItem;
use App\Inventory\Application\AddInventoryItem\AddInventoryItemInput;
use App\Inventory\Application\FindInventoryItemBySize\FindInventoryItemBySize;
use App\Inventory\Application\FindInventoryItemBySize\FindInventoryItemBySizeInput;
use App\Inventory\Application\GeneratePurchaseOrder\GeneratePurchaseOrder;
use App\Inventory\Application\GeneratePurchaseOrder\GeneratePurchaseOrderInput;
use App\Inventory\Application\GeneratePurchaseRecommendations\GeneratePurchaseRecommendations;
use App\Inventory\Application\GeneratePurchaseRecommendations\GeneratePurchaseRecommendationsInput;
use App\Inventory\Application\GetInventoryItemsAnalytics\GetInventoryItemsAnalytics;
use App\Inventory\Application\GetInventoryItemsAnalytics\GetInventoryItemsAnalyticsInput;
use App\Inventory\Application\ListInventoryItems\ListInventoryItems;
use App\Inventory\Application\ListInventoryItems\ListInventoryItemsInput;
use App\Inventory\Application\MailPurchaseOrderToSupplier\MailPurchaseOrderToSupplier;
use App\Inventory\Application\MailPurchaseOrderToSupplier\MailPurchaseOrderToSupplierInput;
use App\Inventory\Application\PlanAndPlacePurchaseOrders\PlanAndPlacePurchaseOrders;
use App\Inventory\Application\PlanAndPlacePurchaseOrders\PlanAndPlacePurchaseOrdersInput;
use App\Inventory\Application\PrintInventoryItemLabel\PrintInventoryItemLabel;
use App\Inventory\Application\PrintInventoryItemLabel\PrintInventoryItemLabelInput;
use App\Inventory\Application\RemoveInventoryItem\RemoveInventoryItem;
use App\Inventory\Application\RemoveInventoryItem\RemoveInventoryItemInput;
use App\Inventory\Application\SellInventoryItem\SellInventoryItem;
use App\Inventory\Application\SellInventoryItem\SellInventoryItemInput;
use App\Inventory\Domain\Exceptions\SupplierNotFoundException;
use App\Inventory\Domain\Repositories\InventoryItemRepositoryInterface;
use App\Inventory\Domain\Repositories\PurchaseOrderRepositoryInterface;
use App\Inventory\Domain\Repositories\PurchaseScheduleRepositoryInterface;
use App\Inventory\Domain\Repositories\PurchaseTaskRepositoryInterface;
use App\Inventory\Domain\Repositories\SupplierRepositoryInterface;
use App\Inventory\Domain\Services\LabelGeneratorServiceInterface;
use App\Inventory\Domain\Services\MailerServiceInterface;
use App\Inventory\Domain\Services\ProductCatalogServiceInterface;
use App\Inventory\Domain\Services\PurchaseOrderMailManagerInterface;
use App\Inventory\Domain\Services\PurchaseOrderTemplateManagerInterface;
use App\Inventory\Domain\Services\PurchaseTaskExecutionServiceInterface;
use App\Inventory\Infrastructure\Webhooks\WebhookManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use JetBrains\PhpStorm\ArrayShape;

class InventoryItemController
{
    protected InventoryItemRepositoryInterface $inventoryItemRepository;
    protected LabelGeneratorServiceInterface $labelGeneratorService;
    protected SupplierRepositoryInterface $supplierRepository;
    protected ProductCatalogServiceInterface $productCatalogService;
    protected PurchaseOrderTemplateManagerInterface $purchaseOrderTemplateManager;
    protected MailerServiceInterface $mailerService;
    protected PurchaseOrderRepositoryInterface $purchaseOrderRepository;
    protected PurchaseTaskExecutionServiceInterface $purchaseOrderPlanningService;
    protected PurchaseScheduleRepositoryInterface $purchaseScheduleRepository;
    protected PurchaseTaskRepositoryInterface $purchaseOrderTaskRepository;

    public function __construct()
    {
        $this->inventoryItemRepository = App::make(InventoryItemRepositoryInterface::class);
        $this->supplierRepository = App::make(SupplierRepositoryInterface::class);
        $this->labelGeneratorService = App::make(LabelGeneratorServiceInterface::class);
        $this->productCatalogService = App::make(ProductCatalogServiceInterface::class);
        $this->mailerService = App::make(MailerServiceInterface::class);
        $this->purchaseOrderTemplateManager = App::make(PurchaseOrderTemplateManagerInterface::class);
        $this->purchaseOrderRepository = App::make(PurchaseOrderRepositoryInterface::class);
        $this->purchaseScheduleRepository = App::make(PurchaseScheduleRepositoryInterface::class);
        $this->purchaseOrderPlanningService = App::make(PurchaseTaskExecutionServiceInterface::class);
        $this->purchaseOrderTaskRepository = App::make(PurchaseTaskRepositoryInterface::class);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function list(Request $request): array
    {
        $inventoryItemsInput = new ListInventoryItemsInput([
            'pagingOptions' => [
                'page' => $request->get('page'),
                'itemsPerPage' => $request->get('itemsPerPage'),
                'sortBy' => $request->get('sortBy', []),
                'sortDesc' => $request->get('sortDesc', []),
                'search' => $request->get('search'),
                'filters' => $request->get('filters', []),
            ]
        ]);

        $useCase = new ListInventoryItems($this->inventoryItemRepository);

        $result = $useCase->execute($inventoryItemsInput);
        return $result->inventoryItems->toArray();
    }

    /**
     * @param Request $request
     * @return array
     */
    public function add(Request $request): array
    {
        $productCode = $request->input('product_code');
        $description = $request->input('description');
        $brand = $request->input('brand');
        $color = $request->input('color');
        $width = $request->input('width');
        $length = $request->input('length');
        $height = $request->input('height');
        $location = $request->input('location');
        $stock = $request->input('quantity');
        $type = $request->input('type');

        $input = new AddInventoryItemInput([
            'product_code' => $productCode,
            'description' => $description,
            'brand' => $brand,
            'color' => $color,
            'width' => [
                'quantity' => $width,
                'measure' => 'cm'
            ],
            'length' => [
                'quantity' => $length,
                'measure' => 'cm'
            ],
            'height' => [
                'quantity' => ! $height ? 0 : $height,
                'measure' => 'cm'
            ],
            'location' => $location,
            'stock' => $stock,
            'employee_who_added_inventory_item' => Auth::id(),
            'type' => $type
        ]);

        $useCase = new AddInventoryItem($this->inventoryItemRepository);
        $result = $useCase->execute($input);

        return [
            'success' => true,
            'data' => $result->inventoryItem()->toArray()
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    #[ArrayShape(['success' => "bool", 'data' => "array"])] public function print(Request $request): array
    {
        $registrationNumbers = $request->get('registration_numbers');

        $input = new PrintInventoryItemLabelInput($registrationNumbers);
        $useCase = new PrintInventoryItemLabel($this->inventoryItemRepository, $this->labelGeneratorService);;

        $result = $useCase->execute($input);

        return [
            'success' => true,
            'data' => [
                'label' => $result->label()
            ]
        ];
    }

    /**
     * @param Request $request
     * @return bool[][]
     */
    #[ArrayShape(['payload' => "bool[]"])] public function remove(Request $request): array
    {
        $registrationNumbers = $request->get('registration_numbers');

        $input = new RemoveInventoryItemInput([
            "registration_numbers" => $registrationNumbers
        ]);

        $useCase = new RemoveInventoryItem($this->inventoryItemRepository);

        $result = $useCase->execute($input);

        return [
            'payload' => [
                'success' => true
            ]
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function sell(Request $request): array
    {
        try {
            $requestInput = $request->all();
            $requestInput['sold_price'] = (float) str_replace(',', '.', $requestInput['sold_price']);
            $input = new SellInventoryItemInput($requestInput);

            $useCase = new SellInventoryItem($this->inventoryItemRepository);

            $result = $useCase->execute($input);

            return [
                'success' => true
            ];
        } catch (\Exception $e)
        {
            Log::notice($e->getMessage());

            // TODO: Think about what message to return to client
            return [
                'error' => $e->getMessage(),
                'success' => false
            ];
        }

    }

    public function getInventoryItemStock(Request $request, string $productCode)
    {
        $inventoryItemRepository = App::make(InventoryItemRepositoryInterface::class, [
            'name' => 'picqer'
        ]);

        $inventoryItem = $inventoryItemRepository->findOneByProductCode($productCode);

        $response['data']['ean'] = $productCode;

        if ($inventoryItem === null)
        {
            $response['data']['stock'] = 0;
            $response['data']['found'] = false;
        } else {
            $response['data']['stock'] = $inventoryItem->stock()->free();
            $response['data']['found'] = true;
        }

        $response['success'] = true;
        $response['error'] = null;

        return $response;
    }

    public function handleWebhook(Request $request, string $webhookName)
    {
        WebhookManager::handle($request, $webhookName);
    }

    public function getInventoryItemsAnalytics(Request $request)
    {
        $useCase = new GetInventoryItemsAnalytics($this->inventoryItemRepository);
        $result = $useCase->execute(new GetInventoryItemsAnalyticsInput(
            $request->input('type'),
            $request->input('start_date'),
            $request->input('end_date'))
        );

        if(!$result->analytics) {
            return response()->json([
                'message' => 'No analytics data available'
            ], 404);
        }

        return response()->json([
            'dates' => $result->analytics->getDates(),
            'columns' => $result->analytics->getColumns(),
            'values' => $result->analytics->getValues()
        ]);
    }

    public function generatePurchaseRecommendations(Request $request)
    {
        $inventoryItemRepository = App::make(InventoryItemRepositoryInterface::class, [
            'name' => 'picqer'
        ]);

        $suppliers = $request->input('suppliers');

        $generatePurchasingAdvice = new GeneratePurchaseRecommendations($inventoryItemRepository, $this->supplierRepository);
        $generatePurchasingAdviceInput = new GeneratePurchaseRecommendationsInput([
            'suppliers' => $suppliers
        ]);
        $generatePurchasingAdviceResult = $generatePurchasingAdvice->execute($generatePurchasingAdviceInput);
        $response['payload']['purchase_recommendations'] = $generatePurchasingAdviceResult->purchasingRecommendations()->values()->toArray();

        return $response;
    }

    public function generatePurchaseOrder(Request $request)
    {
        $inventoryItemRepository = App::make(InventoryItemRepositoryInterface::class, [
            'name' => 'picqer'
        ]);

        $purchaseRecommendations = $request->input('purchase_recommendations');
        $includedData = $request->input('included_data');
        $generatePurchaseOrder = new GeneratePurchaseOrder($inventoryItemRepository, $this->supplierRepository, $this->productCatalogService, $this->purchaseOrderTemplateManager, $this->purchaseOrderRepository, $this->purchaseScheduleRepository);

        $generatePurchaseOrderInput = new GeneratePurchaseOrderInput([
            'included_data' => $includedData,
            'purchase_recommendations' => $purchaseRecommendations
        ]);

        $generatePurchaseOrderResult = $generatePurchaseOrder->execute($generatePurchaseOrderInput);
        $response['payload']['purchase_order'] = $generatePurchaseOrderResult->purchaseOrder()->toArray();
        $response['payload']['purchase_order_export'] = $generatePurchaseOrderResult->purchaseOrderExport()->toArray();

        return $response;
    }

    public function mailPurchaseOrderToSupplier(Request $request)
    {
        $inventoryItemRepository = App::make(InventoryItemRepositoryInterface::class, [
            'name' => 'picqer'
        ]);

        $purchaseRecommendations = $request->input('purchase_recommendations');
        $includedData = $request->input('included_data');

        $purchaseOrderMailManager = App::make(PurchaseOrderMailManagerInterface::class);

        $mailPurchaseOrderToSupplier = new MailPurchaseOrderToSupplier($this->mailerService, $inventoryItemRepository, $this->supplierRepository, $this->productCatalogService, $this->purchaseOrderTemplateManager, $purchaseOrderMailManager, $this->purchaseOrderRepository, $this->purchaseScheduleRepository);
        $mailPurchaseOrderToSupplierInput = new MailPurchaseOrderToSupplierInput([
            'included_data' => $includedData,
            'purchase_recommendations' => $purchaseRecommendations
        ]);

        $mailPurchaseOrderToSupplierResult = $mailPurchaseOrderToSupplier->execute($mailPurchaseOrderToSupplierInput);

        $response['success'] = true;

        return $response;
    }

    public function streamPurchaseOrder(Request $request, string $purchaseOrderReference)
    {
        if (! $request->hasValidSignature())
        {
            abort(401);
        }

        $path = Storage::path('purchase_orders/' . $purchaseOrderReference . '.xlsx');

        return response()->download(
            $path, sprintf("Inkoopbestelling Home Design Shops (%s).xlsx", $purchaseOrderReference)
        );
    }

    /**
     * @throws \App\Inventory\Domain\Exceptions\PlanPurchaseOrderInputValidationException
     * @throws SupplierNotFoundException
     */
    public function planPurchaseOrders(Request $request)
    {
        $allSuppliersAndTagse = $request->input('all_suppliers_and_tags');
        $suppliers = $request->input('suppliers');

        $planPurchaseOrders = new PlanAndPlacePurchaseOrders($this->supplierRepository, $this->purchaseScheduleRepository, $this->purchaseOrderPlanningService, $this->purchaseOrderTaskRepository);
        $planPurchaseOrdersInput = new PlanAndPlacePurchaseOrdersInput([
            'all_suppliers_and_tags' => $allSuppliersAndTagse,
            'suppliers' => $suppliers
        ]);

        $planPurchaseOrders->execute($planPurchaseOrdersInput);

        $response['success'] = true;
        $response['data'] = null;

        return $response;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function findBySize(Request $request): array
    {
        try
        {
            $input = new FindInventoryItemBySizeInput($request->all());
            $useCase = new FindInventoryItemBySize($this->inventoryItemRepository);
            $result = $useCase->execute($input);

            $response['success'] = true;
            $response['data'] = $result->inventoryItem()->toArray();
            return $response;
        }
        catch (\Exception $e)
        {
            $response['success'] = false;
            $response['data']  = null;
            $response['error']  = $e->getMessage();
            return $response;
        }
    }
}
