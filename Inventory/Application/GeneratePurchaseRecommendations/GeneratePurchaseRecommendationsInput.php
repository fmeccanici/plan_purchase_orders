<?php


namespace App\Inventory\Application\GeneratePurchaseRecommendations;

use HomeDesignShops\LaravelDdd\Support\Input;
use Illuminate\Support\Arr;

final class GeneratePurchaseRecommendationsInput extends Input
{
    /**
     * @var array The PASVL validation rules
     */
    protected $rules = [
        'suppliers' => [
            '*' => [
                'id' => ':string',
                'tags' => [
                    '*' => ':string'
                ]
            ]
        ]
    ];

    protected array $suppliers;

    /**
     * GeneratePurchasingAdviceInput constructor.
     */
    public function __construct($input)
    {
        $this->validate($input);
        $this->suppliers = Arr::get($input, 'suppliers');
    }

    public function suppliers(): array
    {
        return $this->suppliers;
    }
}
