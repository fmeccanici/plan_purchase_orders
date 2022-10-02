<?php

namespace Tests\Unit\Warehouse\Mocks\Picqer\BatchPicklists;

use Illuminate\Contracts\Support\Arrayable;

class PicqerProductField implements Arrayable
{
    protected string $idProductField;
    protected string $title;
    protected string $value;

    /**
     * @param string $idProductField
     * @param string $title
     * @param string $value
     */
    public function __construct(string $idProductField, string $title, string $value)
    {
        $this->idProductField = $idProductField;
        $this->title = $title;
        $this->value = $value;
    }

    public function toArray()
    {
        return [
            'idproductfield' => $this->idProductField,
            'title' => $this->title,
            'value' => $this->value
        ];
    }
}
