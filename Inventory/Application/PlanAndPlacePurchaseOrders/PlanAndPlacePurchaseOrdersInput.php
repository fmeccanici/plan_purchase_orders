<?php


namespace App\Inventory\Application\PlanAndPlacePurchaseOrders;

use App\Inventory\Domain\Exceptions\PlanPurchaseOrderInputValidationException;
use HomeDesignShops\LaravelDdd\Support\Input;
use Illuminate\Support\Arr;

final class PlanAndPlacePurchaseOrdersInput extends Input
{
    /**
     * @var array The PASVL validation rules
     */
    protected $rules = [
        'all_suppliers_and_tags' => ':bool',
        'suppliers?' => [
            '*' => [
                'id' => ':string',
                'tags' => [
                    '*' => ':string'
                ]
            ]
        ]
    ];

    protected bool $allSuppliersAndTags;
    protected null|array $suppliers;

    /**
     * PlanPurchaseOrderPlacementsInput constructor.
     * @throws PlanPurchaseOrderInputValidationException
     */
    public function __construct($input)
    {
        $this->validate($input);

        $this->allSuppliersAndTags = Arr::get($input, 'all_suppliers_and_tags');

        if ($this->allSuppliersAndTags)
        {
            $this->suppliers = null;
        } else {
            $this->suppliers = Arr::get($input, 'suppliers');

            if ($this->suppliers === null)
            {
                throw new PlanPurchaseOrderInputValidationException('When not planning for all suppliers and tags you should specify the suppliers and tags');
            }

        }
    }

    public function allSuppliersAndTags(): bool
    {
        return $this->allSuppliersAndTags;
    }

    public function suppliers(): ?array
    {
        return $this->suppliers;
    }
}
