<?php

namespace App\Inventory\Infrastructure\Persistence\Picqer\Suppliers;

use Illuminate\Contracts\Support\Arrayable;

class PicqerSupplier implements Arrayable
{
    protected string $name;
    protected int $idSupplier;

    /**
     * @param string $name
     * @param int $idSupplier
     */
    public function __construct(string $name, int $idSupplier)
    {
        $this->name = $name;
        $this->idSupplier = $idSupplier;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function idSupplier(): int
    {
        return $this->idSupplier;
    }

    public function toArray()
    {
        return [
            'name' => $this->name,
            'idsupplier' => $this->idSupplier
        ];
    }
}
