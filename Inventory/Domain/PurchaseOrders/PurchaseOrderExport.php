<?php

namespace App\Inventory\Domain\PurchaseOrders;

use App\SharedKernel\CleanArchitecture\Entity;
use Illuminate\Contracts\Support\Arrayable;

class PurchaseOrderExport extends Entity implements Arrayable
{
    protected string $path;
    protected string $url;

    /**
     * PackingList constructor.
     * @param string $path
     * @param string $url
     */
    public function __construct(string $path, string $url)
    {
        $this->path = $path;
        $this->url = $url;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function url(): string
    {
        return $this->url;
    }

    protected function cascadeSetIdentity(int|string $id): void
    {
        // Nothing to do
    }

    public function toArray()
    {
        return [
            'path' => $this->path,
            'url' => $this->url
        ];
    }
}
