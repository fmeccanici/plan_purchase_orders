<?php

namespace App\Inventory\Domain\InventoryItems;

use App\Inventory\Domain\Services\LabelGeneratorServiceInterface;
use App\SharedKernel\CleanArchitecture\AggregateRoot;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

class InventoryItem extends AggregateRoot implements Arrayable
{
    public const TYPES = ['doormat', 'squid'];

    protected string $productCode;
    protected ?string $externalProductCode;
    protected string $description;
    protected string $brand;
    protected string $color;
    protected Size $width;
    protected Size $length;
    protected Size $height;
    protected string $location;
    protected Stock $stock;
    protected int $employeeWhoAddedInventoryItem;
    protected ?float $soldPrice = null;
    protected ?CarbonImmutable $soldAt = null;
    protected LabelGeneratorServiceInterface $labelGeneratorService;
    protected CarbonImmutable $createdAt;
    protected int|string|null $registrationNumber = null;
    protected string $type;
    protected ?Warehouse $warehouse;
    protected Collection $tags;
    protected PurchaseSettings $purchaseSettings;
    protected ?int $supplierId;

    /**
     * @param int|string|null $registrationNumber
     * @param string $productCode
     * @param string $description
     * @param string $brand
     * @param string $color
     * @param int $width
     * @param string $widthMeasure
     * @param int $length
     * @param string $lengthMeasure
     * @param int $height
     * @param string $heightMeasure
     * @param string $location
     * @param Stock $stock
     * @param int $employeeWhoAddedInventoryItem
     * @param LabelGeneratorServiceInterface $labelGeneratorService
     * @param CarbonImmutable $createdAt
     * @param string $type
     * @param Collection $tags
     * @param PurchaseSettings $purchaseSettings
     * @param int|null $supplierId
     * @param Warehouse|null $warehouse
     * @param float|null $soldPrice
     * @param ?CarbonImmutable $soldAt
     * @param string|null $externalProductCode
     */
    public function __construct(int|string|null $registrationNumber, string $productCode, string $description, string $brand, string $color, int $width, string $widthMeasure, int $length, string $lengthMeasure, int $height, string $heightMeasure, string $location, Stock $stock, int $employeeWhoAddedInventoryItem, LabelGeneratorServiceInterface $labelGeneratorService, CarbonImmutable $createdAt, string $type, Collection $tags, PurchaseSettings $purchaseSettings, ?int $supplierId, ?Warehouse $warehouse = null, ?float $soldPrice = null, ?CarbonImmutable $soldAt = null, ?string $externalProductCode = null)
    {
        $this->registrationNumber = $registrationNumber;
        $this->productCode = $productCode;
        $this->externalProductCode = $externalProductCode;
        $this->description = $description;
        $this->brand = $brand;
        $this->color = $color;
        $this->width = new Size($width, $widthMeasure);
        $this->length = new Size($length, $lengthMeasure);
        $this->height = new Size($height, $heightMeasure);
        $this->location = $location;
        $this->stock = $stock;
        $this->employeeWhoAddedInventoryItem = $employeeWhoAddedInventoryItem;
        $this->soldPrice = $soldPrice;
        $this->soldAt = $soldAt;
        $this->labelGeneratorService = $labelGeneratorService;
        $this->supplierId = $supplierId;
        $this->purchaseSettings = $purchaseSettings;
        $this->createdAt = $createdAt;
        $this->warehouse = $warehouse;
        $this->type = $type;
        $this->tags = $tags;
    }

    public function registrationNumber(): int|string|null
    {
        return $this->registrationNumber;
    }

    public function changeRegistrationNumber(int|string|null $registrationNumber)
    {
        $this->registrationNumber = $registrationNumber;
    }

    public function productCode(): string
    {
        return $this->productCode;
    }

    public function externalProductCode(): ?string
    {
        return $this->externalProductCode;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function brand(): string
    {
        return $this->brand;
    }

    public function color(): string
    {
        return $this->color;
    }

    public function width(): Size
    {
        return $this->width;
    }

    public function length(): Size
    {
        return $this->length;
    }

    public function height(): Size
    {
        return $this->height;
    }

    public function location(): string
    {
        return $this->location;
    }

    public function stock(): Stock
    {
        return $this->stock;
    }

    public function employeeWhoAddedInventoryItem(): int
    {
        return $this->employeeWhoAddedInventoryItem;
    }

    public function soldPrice(): ?float
    {
        return $this->soldPrice;
    }

    public function soldAt(): ?CarbonImmutable
    {
        return $this->soldAt;
    }

    public function createdAt(): ?CarbonImmutable
    {
        return $this->createdAt;
    }

    public function sell(CarbonImmutable $soldAt, float $soldPrice): void
    {
        $this->soldAt = $soldAt;
        $this->soldPrice = $soldPrice;
        $this->stock = new Stock(0, $this->stock->reserved(), $this->stock->desired());
    }

    public function decreaseStock(int $stock): InventoryItem
    {
        $this->stock = $this->stock->decrease($stock);
        return $this;
    }

    public function increaseStock(int $stock): InventoryItem
    {
        $this->stock = $this->stock->increase($stock);
        return $this;
    }

    public function sold(): bool
    {
        return $this->soldPrice !== null && $this->soldAt !== null;
    }

    public function changeStock(int $stock)
    {
        $this->stock = new Stock($stock, $this->stock->reserved(), $this->stock->desired());
    }

    public function warehouse(): ?Warehouse
    {
        return $this->warehouse;
    }

    public function type(): string
    {
        return $this->type;
    }

    /**
     * @param InventoryItem $other
     * @return void
     */
    public function synchronizeStockTo(InventoryItem $other)
    {
        $other->changeStock($this->stock->free());
    }

    public function addTag(string $tag)
    {
        $this->tags->push($tag);
    }

    public function hasTag(string $tag): bool
    {
        return $this->tags->contains($tag);
    }

    public function tags(): Collection
    {
        return $this->tags;
    }

    public function supplierId(): ?int
    {
        return $this->supplierId;
    }

    public function purchaseSettings(): PurchaseSettings
    {
        return $this->purchaseSettings;
    }

    public static function fromArray(array $inventoryItem): self
    {
        $registrationNumber = $inventoryItem["registration_number"];
        $productCode = $inventoryItem["product_code"];
        $description = $inventoryItem["description"];
        $brand = $inventoryItem["brand"];
        $color = $inventoryItem["color"];
        $widthQuantity = $inventoryItem["width"]["quantity"];
        $widthMeasure = $inventoryItem["width"]["measure"];
        $lengthQuantity = $inventoryItem["length"]["quantity"];
        $lengthMeasure = $inventoryItem["length"]["measure"];
        $heightQuantity = $inventoryItem["height"]["quantity"];
        $heightMeasure = $inventoryItem["height"]["measure"];
        $location = $inventoryItem["location"];
        $type = $inventoryItem["type"];
        $employeeWhoAddedInventoryItem = $inventoryItem["employee_who_added_inventory_item"];

        $soldPrice = Arr::get($inventoryItem, 'sold_price');

        // TODO: Refactor AddInventoryItem use case zodat deze ook free stock etc. heeft: Task 19818: Refactor AddInventoryItem use case zodat deze ook free stock etc. heeft
        $purchaseSettings = Arr::get($inventoryItem, 'purchase_settings', new PurchaseSettings());
        $stock = new Stock(Arr::get($inventoryItem, 'stock'), 0, 0);

        $soldAt = Arr::get($inventoryItem, "sold_at");
        if ($soldAt)
        {
            $soldAt = CarbonImmutable::parse($inventoryItem["sold_at"]);
        }

        $createdAt = Arr::get($inventoryItem, 'created_at');
        if ($createdAt)
        {
            $createdAt = CarbonImmutable::parse($createdAt);
        }

        $labelGeneratorService = App::make(LabelGeneratorServiceInterface::class);


        $tags = collect(Arr::get($inventoryItem, 'tags'));
        $supplierId = Arr::get($inventoryItem, 'supplier_id');

        return new self($registrationNumber, $productCode, $description, $brand, $color, $widthQuantity, $widthMeasure, $lengthQuantity, $lengthMeasure,
                                $heightQuantity, $heightMeasure, $location, $stock, $employeeWhoAddedInventoryItem, $labelGeneratorService, $createdAt, $type, $tags, $purchaseSettings, $supplierId, $soldPrice, $soldAt);
    }

    public function needsStockReplenishment(): bool
    {
        return $this->stock->needsReplenishment();
    }

    public function toArray(): array
    {
        return [
            "registration_number" => $this->registrationNumber,
            "product_code" => $this->productCode,
            "description" => $this->description,
            "brand" => $this->brand,
            "color" => $this->color,
            "width" => [
                "quantity" => $this->width->quantity(),
                "measure" => $this->width->measure()
            ],
            "length" => [
                "quantity" => $this->length->quantity(),
                "measure" => $this->length->measure()
            ],
            "height" => [
                "quantity" => $this->height->quantity(),
                "measure" => $this->height->measure()
            ],
            "location" => $this->location,
            "stock" => $this->stock->toArray(),
            "employee_who_added_inventory_item" => $this->employeeWhoAddedInventoryItem,
            "sold_price" => $this->soldPrice,
            "sold_at" => $this->soldAt?->format("Y-m-d"),
            "created_at" => $this->createdAt->format("Y-m-d"),
            'purchase_settings' => $this->purchaseSettings?->toArray(),
            'supplier_id' => $this->supplierId
        ];
    }

    protected function cascadeSetIdentity(int|string $id): void
    {
        // Nothing to be done
    }
}
