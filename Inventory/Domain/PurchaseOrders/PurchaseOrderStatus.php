<?php

namespace App\Inventory\Domain\PurchaseOrders;

use App\Inventory\Domain\Exceptions\PurchaseStatusException;
use App\SharedKernel\CleanArchitecture\ValueObject;
use Illuminate\Contracts\Support\Arrayable;

class PurchaseOrderStatus extends ValueObject implements Arrayable
{
    public const CONCEPT = 'concept';
    public const PURCHASED = 'purchased';

    protected string $name;

    /**
     * @param string $name
     * @throws PurchaseStatusException
     */
    public function __construct(string $name)
    {
        $this->validate($name);
        $this->name = $name;
    }

    /**
     * @throws PurchaseStatusException
     */
    private function validate(string $name)
    {
        if ($name != self::CONCEPT && $name != self::PURCHASED)
        {
            throw new PurchaseStatusException('Status name ' . $name . ' is invalid');
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    public function concept(): bool
    {
        return $this->name == self::CONCEPT;
    }

    public function purchased(): bool
    {
        return $this->name == self::PURCHASED;
    }

    public function toArray()
    {
        return $this->name;
    }
}
