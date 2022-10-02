<?php

namespace App\Inventory\Domain\PurchaseOrders;

use App\SharedKernel\CleanArchitecture\Entity;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;

class PurchaseOrderLine extends Entity implements Arrayable
{
    protected string $productCode;
    protected ?string $productCodeSupplier;
    protected int $quantity;
    protected string $productName;
    private float $price;
    protected ?int $deliveryWorkDays;

    /**
     * @param string $productCode
     * @param string|null $productCodeSupplier
     * @param string $productName
     * @param int $quantity
     * @param float $price
     * @param int|null $deliveryWorkDays
     */
    public function __construct(string $productCode, ?string $productCodeSupplier, string $productName, int $quantity, float $price, ?int $deliveryWorkDays)
    {
        $this->productCode = $productCode;
        $this->productCodeSupplier = $productCodeSupplier;
        $this->productName = $productName;
        $this->quantity = $quantity;
        $this->price = $price;
        $this->deliveryWorkDays = $deliveryWorkDays;
    }

    public function productCode(): string
    {
        return $this->productCode;
    }

    public function productCodeSupplier(): ?string
    {
        return $this->productCodeSupplier;
    }

    public function productName(): string
    {
        return $this->productName;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function price(): float
    {
        return $this->price;
    }

    public function deliveryDate(?CarbonImmutable $from = null): ?CarbonImmutable
    {
        if ($this->deliveryWorkDays === null)
        {
            return null;
        }

        if ($from === null)
        {
            $from = CarbonImmutable::now();
        }

        $deliveryDate = clone $from;

        $i = 0;

        while ($i < $this->deliveryWorkDays)
        {
            if ($deliveryDate->addDay()->isWeekday())
            {
                $deliveryDate = $deliveryDate->addDay();
                $i++;
            } else if ($deliveryDate->addDay()->isWeekend())
            {
                $deliveryDate = $deliveryDate->addDay();
            }
        }

        return $deliveryDate;
    }

    public function deliveryWorkDays(): ?int
    {
        return $this->deliveryWorkDays;
    }

    public function toArray()
    {
        return [
            'product_code' => $this->productCode,
            'product_code_supplier' => $this->productCodeSupplier,
            'product_name' => $this->productName,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'delivery_date' => $this->deliveryDate()?->toDateString()
        ];
    }

    protected function cascadeSetIdentity(int|string $id): void
    {
        // Nothing to do
    }
}
