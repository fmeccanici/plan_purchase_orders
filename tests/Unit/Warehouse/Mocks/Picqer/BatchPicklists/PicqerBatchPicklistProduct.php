<?php

namespace Tests\Unit\Warehouse\Mocks\Picqer\BatchPicklists;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

class PicqerBatchPicklistProduct implements Arrayable
{
    protected string $idProduct;
    protected string $name;
    protected string $productCode;
    protected string $productCodeSupplier;
    protected ?string $image;
    protected array $barCodes;

    /**
     * @var Collection<PicqerProductField>
     */
    protected Collection $productFields;
    protected string $stockLocation;

    /**
     * @var Collection<PicqerBatchPicklistPicklistInsideProducts>
     */
    protected Collection $picklists;

    /**
     * @param string $idProduct
     * @param string $name
     * @param string $productCode
     * @param string $productCodeSupplier
     * @param string|null $image
     * @param array $barCodes
     * @param Collection $productFields
     * @param string $stockLocation
     * @param Collection $picklists
     */
    public function __construct(string $idProduct, string $name, string $productCode, string $productCodeSupplier, ?string $image, array $barCodes, Collection $productFields, string $stockLocation, Collection $picklists)
    {
        $this->idProduct = $idProduct;
        $this->name = $name;
        $this->productCode = $productCode;
        $this->productCodeSupplier = $productCodeSupplier;
        $this->image = $image;
        $this->barCodes = $barCodes;
        $this->productFields = $productFields;
        $this->stockLocation = $stockLocation;
        $this->picklists = $picklists;
    }

    public function toArray()
    {
        return [
            'idproduct' => $this->idProduct,
            'name' => $this->name,
            'productcode' => $this->productCode,
            'productcode_supplier' => $this->productCodeSupplier,
            'image' => $this->image,
            'barcodes' => $this->barCodes,
            'productfields' => $this->productFields->toArray(),
            'stock_location' => $this->stockLocation,
            'picklists' => $this->picklists->toArray()
        ];
    }
}
