<?php

namespace App\Inventory\Domain\InventoryItems;

use App\SharedKernel\CleanArchitecture\Entity;

class Warehouse extends Entity
{
    protected function cascadeSetIdentity(int|string $id): void
    {
        // Nothing to do here
    }
}
