<?php


namespace App\Inventory\Infrastructure\Webhooks;

use Illuminate\Support\Collection;


class PicqerWebhook extends Webhook
{
    public function eventName(): string
    {
        return $this->request->input('event');
    }

    public function eventListeners(): Collection
    {
        $eventListenerNames = config('inventory')['webhooks'][$this->name()]['events'][$this->eventName()];
        return collect($eventListenerNames)->map(function (string $eventListenerName) {
                return new $eventListenerName();
        });
    }

    public function name(): string
    {
        return "picqer";
    }
}
