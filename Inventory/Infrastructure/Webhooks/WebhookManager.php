<?php


namespace App\Inventory\Infrastructure\Webhooks;

use Illuminate\Http\Request;

class WebhookManager
{
    public static function handle(Request $request, string $webhookName)
    {
        if (! $webhookClass = config('inventory.webhooks.'.$webhookName.".handler"))
        {
            return;
        }

        $webhook = new $webhookClass($request);
        $webhook->handle();
    }
}
