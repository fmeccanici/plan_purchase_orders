<?php

namespace App\Inventory\Domain\Repositories;

use App\Inventory\Domain\Suppliers\Supplier;
use Illuminate\Support\Collection;

interface SupplierRepositoryInterface
{
    public function findOneById(int $id): ?Supplier;
    public function searchOneByName(string $name): ?Supplier;
    public function save(Supplier $supplier): Supplier;
    public function findAll(): Collection;
}
