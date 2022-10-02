<?php

namespace App\Inventory\Infrastructure\Persistence\InMemory\Repositories;

use App\Inventory\Domain\Repositories\SupplierRepositoryInterface;
use App\Inventory\Domain\Suppliers\Supplier;
use Illuminate\Support\Collection;

class InMemoryCollectionSupplierRepository implements SupplierRepositoryInterface
{
    protected Collection $suppliers;

    public function __construct()
    {
        $this->suppliers = collect();
    }

    public function findOneById(int $id): ?Supplier
    {
        return $this->suppliers->first(function (Supplier $supplier) use ($id) {
            return $supplier->identity() == $id;
        });
    }

    public function save(Supplier $supplier): Supplier
    {
        $this->suppliers->push($supplier);
        return $supplier;
    }

    public function findAll(): Collection
    {
        return $this->suppliers;
    }

    public function searchOneByName(string $name): ?Supplier
    {
        return $this->suppliers->first(function (Supplier $supplier) use ($name) {
                return $supplier->name() == $name;
        });
    }
}
