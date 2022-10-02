<?php


namespace App\Inventory\Application\MailPurchaseOrderToSupplier;

use HomeDesignShops\LaravelDdd\Support\Input;
use Illuminate\Support\Arr;

final class MailPurchaseOrderToSupplierInput extends Input
{

    /**
     * @var array The PASVL validation rules
     */
    protected $rules = [
        'included_data' => [
            'product_code?' => ':string',
            'product_name?' => ':string',
            'product_code_supplier?' => ':string',
            'quantity?' => ':string',
            'delivery_work_days?' => ':string'
        ],
        'purchase_recommendations' => [
            '*' => [
                'product_code' => ':string',
                'amount_to_be_purchased' => ':number :int',
                'supplier_id' => ':string',
                'product_code_supplier' => ':string?',
                'tag' => ':string'
            ]
        ]
    ];

    protected array $includedData;
    protected array $purchaseRecommendations;

    /**
     * MailPurchaseOrderToSupplierInput constructor.
     */
    public function __construct($input)
    {
        $this->validate($input);

        $this->includedData = Arr::get($input, 'included_data');
        $this->purchaseRecommendations = Arr::get($input, 'purchase_recommendations');
    }

    public function purchaseRecommendations(): array
    {
        return $this->purchaseRecommendations;
    }

    public function includedData(): array
    {
        return $this->includedData;
    }
}
