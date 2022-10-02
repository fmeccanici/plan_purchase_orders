<?php

namespace App\Inventory\Domain\PurchaseRecommendations;

use App\Inventory\Domain\InventoryItems\PurchaseSettings;
use App\Inventory\Domain\InventoryItems\Stock;
use App\SharedKernel\CleanArchitecture\ValueObject;
use Illuminate\Contracts\Support\Arrayable;

class PurchaseRecommendation extends ValueObject implements Arrayable
{
    protected Stock $stock;
    protected PurchaseSettings $purchaseSettings;
    protected string $productCode;
    protected ?string $productCodeSupplier;
    protected string $supplierId;
    protected string $tag;

    public function __construct(Stock $stock, PurchaseSettings $purchaseSettings, string $productCode, ?string $productCodeSupplier, string $supplierId, string $tag)
    {
        $this->stock = $stock;
        $this->purchaseSettings = $purchaseSettings;
        $this->productCode = $productCode;
        $this->productCodeSupplier = $productCodeSupplier;
        $this->supplierId = $supplierId;
        $this->tag = $tag;
    }

    public function productCode(): string
    {
        return $this->productCode;
    }

    public function productCodeSupplier(): ?string
    {
        return $this->productCodeSupplier;
    }

    public function supplierId(): string
    {
        return $this->supplierId;
    }

    public function tag(): string
    {
        return $this->tag;
    }

    /**
     * @return int
     * Returns the amount to be purchased
     */
    public function recommend(): int
    {
        $amountToBePurchased = $this->stock->desired() - $this->stock->free() - $this->stock->toBeReceived();

        if ($amountToBePurchased > 0) {
            $amountToBePurchased = max($amountToBePurchased, $this->purchaseSettings->minimumPurchasingAmount());

            // TODO: Refactor using intdiv: Task 19932: Refactor recommend() functie op PurchaseRecommendation gebruikmakend van intdiv
            if ($this->purchaseSettings->purchaseInMultiplesOf() !== 0) {
                while ($amountToBePurchased % $this->purchaseSettings->purchaseInMultiplesOf() > 0) {
                    $amountToBePurchased++;
                }
            }

            return $amountToBePurchased;
        }

        return 0;
    }

    public function toArray()
    {
        return [
            'product_code' => $this->productCode,
            'amount_to_be_purchased' => $this->recommend(),
            'supplier_id' => $this->supplierId,
            'product_code_supplier' => $this->productCodeSupplier,
            'tag' => $this->tag
        ];
    }
}
