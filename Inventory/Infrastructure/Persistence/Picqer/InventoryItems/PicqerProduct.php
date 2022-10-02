<?php

namespace App\Inventory\Infrastructure\Persistence\Picqer\InventoryItems;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

class PicqerProduct implements Arrayable
{
    protected int $idProduct;
    protected string $productCode;

    /**
     * @var Collection<PicqerStock>
     */
    protected Collection $stock;
    protected Collection $tags;
    protected string $type;
    protected string $name;
    protected ?int $purchaseInMultiplesOf;
    protected ?int $minimumPurchasingAmount;
    protected ?int $idSupplier;
    protected int $desiredStock;
    protected ?string $productCodeSupplier;
    protected int $orderLevel;

    /**
     * @param int $idProduct
     * @param string $productCode
     * @param Collection<PicqerStock> $stock
     * @param int $desiredStock
     * @param int $orderLevel
     * @param Collection $tags
     * @param string $type
     * @param string $name
     * @param int|null $purchaseInMultiplesOf
     * @param int|null $minimumPurchasingAmount
     * @param int|null $idSupplier
     * @param string|null $productCodeSupplier
     */
    public function __construct(int $idProduct, string $productCode, Collection $stock, int $desiredStock, int $orderLevel, Collection $tags, string $type, string $name, ?int $purchaseInMultiplesOf, ?int $minimumPurchasingAmount, ?int $idSupplier, ?string $productCodeSupplier)
    {
        $this->idProduct = $idProduct;
        $this->productCode = $productCode;
        $this->stock = $stock;
        $this->desiredStock = $desiredStock;
        $this->orderLevel = $orderLevel;
        $this->tags = $tags;
        $this->type = $type;
        $this->name = $name;
        $this->purchaseInMultiplesOf = $purchaseInMultiplesOf;
        $this->minimumPurchasingAmount = $minimumPurchasingAmount;
        $this->idSupplier = $idSupplier;
        $this->productCodeSupplier = $productCodeSupplier;
    }

    public function productCode(): string
    {
        return $this->productCode;
    }

    public function productCodeSupplier(): ?string
    {
        return $this->productCodeSupplier;
    }

    public function idProduct(): int
    {
        return $this->idProduct;
    }

    public function stock(): Collection
    {
        return $this->stock;
    }

    public function tags(): Collection
    {
        return $this->tags;
    }

    public function addTag(array $tag)
    {
        $this->tags->push($tag);
    }

    public function addStock(PicqerStock $picqerStock)
    {
        $this->stock->add($picqerStock);
    }

    public function type(): string
    {
        return $this->type;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function minimumPurchaseQuantity(): ?int
    {
        return $this->minimumPurchasingAmount;
    }

    public function purchaseInQuantitiesOf(): ?int
    {
        return $this->purchaseInMultiplesOf;
    }

    public function idSupplier(): ?int
    {
        return $this->idSupplier;
    }

    public function desiredStock(): int
    {
        return $this->desiredStock;
    }

    public function orderLevel(): int
    {
        return $this->orderLevel;
    }

    public function toArray()
    {
        return [
            'idproduct' => $this->idProduct,
            'productcode' => $this->productCode,
            'stock' => $this->stock->toArray(),
            'tags' => $this->tags->toArray(),
            'type' => $this->type,
            'name' => $this->name,
            'minimum_purchase_quantity' => $this->minimumPurchasingAmount,
            'purchase_in_quantities_of' => $this->purchaseInMultiplesOf,
            'idsupplier' => $this->idSupplier
        ];
    }
}
