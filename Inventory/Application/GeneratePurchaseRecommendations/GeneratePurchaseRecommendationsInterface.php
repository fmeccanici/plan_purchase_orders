<?php


namespace App\Inventory\Application\GeneratePurchaseRecommendations;


interface GeneratePurchaseRecommendationsInterface
{
    /**
     * @param GeneratePurchaseRecommendationsInput $input
     * @return GeneratePurchaseRecommendationsResult
     */
    public function execute(GeneratePurchaseRecommendationsInput $input): GeneratePurchaseRecommendationsResult;
}
