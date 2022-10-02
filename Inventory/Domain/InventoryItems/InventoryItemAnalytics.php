<?php

namespace App\Inventory\Domain\InventoryItems;

use Illuminate\Support\Collection;

class InventoryItemAnalytics
{
    /**
     * A Collection of date strings.
     *
     * @var Collection
     */
    protected Collection $dates;

    /**
     * A Collection of column names.
     *
     * @var Collection
     */
    protected Collection $columns;

    /**
     * A Collection of array's with data that matches the count of the columns.
     *
     * @var Collection
     */
    protected Collection $values;

    /**
     * @param Collection $dates
     * @param Collection $columns
     * @param Collection $values
     */
    public function __construct(Collection $dates, Collection $columns, Collection $values)
    {
        $this->dates = $dates;
        $this->columns = $columns;
        $this->values = $values;
    }

    /**
     * @return Collection
     */
    public function getDates(): Collection
    {
        return $this->dates;
    }

    /**
     * @return Collection
     */
    public function getColumns(): Collection
    {
        return $this->columns;
    }

    /**
     * @return Collection
     */
    public function getValues(): Collection
    {
        return $this->values;
    }
}
