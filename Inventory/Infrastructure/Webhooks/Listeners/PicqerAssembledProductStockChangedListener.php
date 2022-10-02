<?php

namespace App\Inventory\Infrastructure\Webhooks\Listeners;

use App\Inventory\Application\ChangeStock\ChangeStock;
use App\Inventory\Application\ChangeStock\ChangeStockInput;
use App\Inventory\Domain\Repositories\InventoryItemRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class PicqerAssembledProductStockChangedListener implements EventListenerInterface
{
    protected InventoryItemRepositoryInterface $inventoryItemRepository;

    public function __construct()
    {
        $this->inventoryItemRepository = App::make(InventoryItemRepositoryInterface::class, [
            'name' => 'sitemanager'
        ]);
    }

    public function handle(Request $request)
    {
        $productCode = $request->input('data.productcode');

        $stock = $request->input('data.stock');

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
