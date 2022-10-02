<?php

namespace App\Inventory\Domain\InventoryItems;

use App\SharedKernel\CleanArchitecture\ValueObject;
use Illuminate\Contracts\Support\Arrayable;

class PurchaseSettings extends ValueObject implements Arrayable
{
    protected int $purchaseInMultiplesOf;
    protected int $minimumPurchasingAmount;

    /**
     * @param int $purchaseInMultiplesOf
     * @param int $minimumPurchasingAmount
     */
    public function __construct(int $purchaseInMultiplesOf = 1, int $minimumPurchasingAmount = 0)
    {
        $this->purchaseInMultiplesOf = $purchaseInMultiplesOf;
        $this->minimumPurchasingAmount = $minimumPurchasingAmount;
    }

    public function minimumPurchasingAmount(): int
    {
        return $this->minimumPurchasingAmount;
    }

    public function purchaseInMultiplesOf(): int
    {
        return $this->purchaseInMultiplesOf;
    }

    public function toArray()
    {
        return [
            'minimum_purchase_amount' => $this->minimumPurchasingAmount,
            'purchase_in_multiples_of' => $this->purchaseInMultiplesOf,
        ];
    }
}
