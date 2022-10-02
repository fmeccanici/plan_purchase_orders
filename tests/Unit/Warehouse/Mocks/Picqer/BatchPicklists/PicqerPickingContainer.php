<?php

namespace Tests\Unit\Warehouse\Mocks\Picqer\BatchPicklists;

use Illuminate\Contracts\Support\Arrayable;

class PicqerPickingContainer implements Arrayable
{
    protected string $name;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function toArray()
    {
        return [
            'name' => $this->name
        ];
    }
}
