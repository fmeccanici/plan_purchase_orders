<?php


namespace App\Inventory\Infrastructure\Webhooks\Listeners;

use Illuminate\Http\Request;

interface EventListenerInterface
{
    public function handle(Request $request);
}
