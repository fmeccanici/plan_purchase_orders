<?php


namespace App\Inventory\Infrastructure\Webhooks;



use Illuminate\Http\Request;
use Illuminate\Support\Collection;

abstract class Webhook
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function handle()
    {
        $this->eventListeners()->each->handle($this->request);
    }

    public abstract function name(): string;
    public abstract function eventName(): string;
    public abstract function eventListeners(): Collection;
}
