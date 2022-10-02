<?php


namespace App\Inventory\Infrastructure\Webhooks\Listeners;

use App\Console\Commands\ClearPurchaseOrdersCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class PicqerPurchaseOrderChangedOrPlacedListener implements EventListenerInterface
{
    /**
     * @param Request $request
     * @return void
     */
    public function handle(Request $request): void
    {
        Artisan::call(ClearPurchaseOrdersCache::class);
    }
}
