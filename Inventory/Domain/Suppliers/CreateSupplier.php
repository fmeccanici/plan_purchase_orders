<?php

namespace App\Inventory\Domain\Suppliers;

class CreateSupplier
{
    public static function peitsman(): Supplier
    {
        return new Supplier('Peitsman', collect(Supplier::PEITSMAN_TAGS));
    }
}
