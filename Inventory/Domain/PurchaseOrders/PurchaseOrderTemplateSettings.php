<?php

namespace App\Inventory\Domain\PurchaseOrders;

use Illuminate\Support\Collection;

class PurchaseOrderTemplateSettings
{
    protected Collection $includedColumns;
    protected Collection $columnNames;

    public function __construct(Collection $includedColumns, Collection $columnNames)
    {
        $this->includedColumns = $includedColumns;
        $this->columnNames = $columnNames;
    }

    public function columnIncluded(string $columnName): bool
    {
        return $this->includedColumns->contains($columnName);
    }

    public function columnNames(): Collection
    {
        return $this->columnNames;
    }
}
