<?php

use App\Inventory\Presentation\Http\Api\InventoryItemController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/list', [InventoryItemController::class, 'list'])->name('list-inventory-items');
Route::post('/add', [InventoryItemController::class, 'add'])->name('add-inventory-item');
Route::post('/print', [InventoryItemController::class, 'print'])->name('print-inventory-item-labels');
Route::post('/remove', [InventoryItemController::class, 'remove'])->name('remove-inventory-items');
Route::post('/sell', [InventoryItemController::class, 'sell'])->name('sell-inventory-item');
Route::post('/find-by-size', [InventoryItemController::class, 'findBySize'])->name('find-inventory-item-by-size');
Route::get('/inventory-items/sync', [InventoryItemController::class, 'synchronizeStock'])->name('sync-stock')->withoutMiddleware('auth:api');
Route::get('/inventory-items/analytics', [InventoryItemController::class, 'getInventoryItemsAnalytics'])->name('get-inventory-items-analytics')->withoutMiddleware('auth:api');
Route::get('/inventory-items/{productCode}/stock', [InventoryItemController::class, 'getInventoryItemStock'])->name('get-inventory-item-stock')->withoutMiddleware('auth:api');
Route::post('/purchase-order/generate', [InventoryItemController::class, 'generatePurchaseOrder'])->name('generate-purchase-order')->withoutMiddleware('auth:api');
Route::post('/purchase-order/mail', [InventoryItemController::class, 'mailPurchaseOrderToSupplier'])->name('mail-purchase-order-to-supplier')->withoutMiddleware('auth:api');
Route::get('/purchase-orders/{purchaseOrderReference}', [InventoryItemController::class, 'streamPurchaseOrder'])->name('stream-purchase-order')->withoutMiddleware('auth:api');
Route::post('/purchase-recommendations/generate', [InventoryItemController::class, 'generatePurchaseRecommendations'])->name('generate-purchase-recommendations')->withoutMiddleware('auth:api');
Route::post('/purchase-orders/plan', [InventoryItemController::class, 'planPurchaseOrders'])->name('plan-purchase-orders')->withoutMiddleware('auth:api');

Route::post("/webhooks/{webhookName?}", [InventoryItemController::class, "handleWebhook"])
    ->withoutMiddleware('auth:api')
    ->name('handle-inventory-webhook');
