<?php


namespace App\Inventory\Application\GeneratePurchaseRecommendations;


use Illuminate\Support\Collection;

final class GeneratePurchaseRecommendationsResult
{
    protected Collection $purchasingRecommendations;

    /**
     * @param Collection $purchasingRecommendations
     */
    public function __construct(Collection $purchasingRecommendations)
    {
        $this->purchasingRecommendations = $purchasingRecommendations;
    }

    public function purchasingRecommendations(): Collection
    {
        return $this->purchasingRecommendations;
    }
}
