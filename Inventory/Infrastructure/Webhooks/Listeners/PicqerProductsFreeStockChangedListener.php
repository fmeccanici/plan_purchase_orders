<?php


namespace App\Inventory\Infrastructure\Webhooks\Listeners;

use App\Inventory\Application\ChangeStock\ChangeStock;
use App\Inventory\Application\ChangeStock\ChangeStockInput;
use App\Inventory\Domain\Repositories\InventoryItemRepositoryInterface;
use App\Inventory\Infrastructure\Persistence\Picqer\InventoryItems\Mappers\PicqerInventoryItemMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class PicqerProductsFreeStockChangedListener implements EventListenerInterface
{
    protected InventoryItemRepositoryInterface $inventoryItemRepository;

    public function __construct()
    {
        $this->inventoryItemRepository = App::make(InventoryItemRepositoryInterface::class, [
            'name' => 'sitemanager'
        ]);
    }

    /**
     * @param Request $request
     * @return void
     */
    public function handle(Request $request): void
    {
        $productCode = $request->input('data.productcode');
        $stock = $request->input('data.stock');
        $tags = $request->input('data.tags');
        $synchronizeStockTag = Arr::get($tags, PicqerInventoryItemMapper::SYNC_STOCK_TAG);

        if ($synchronizeStockTag === null)
        {
            return;
        }

        if (collect($stock)->isEmpty())
        {
            Log::channel('inventory')->error('There is no stock for product with product code ' . $productCode);
            return;
        }

        $freeStock = Arr::get($stock[0], 'freestock');

        $changeStock = new ChangeStock($this->inventoryItemRepository);
        $changeStockInput = new ChangeStockInput([
            'product_code' => $productCode,
            'stock' => $freeStock
        ]);
        $changeStock->execute($changeStockInput);
    }
}
