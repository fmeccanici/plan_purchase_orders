<?php


namespace App\Inventory\Application\PlanAndPlacePurchaseOrders;


interface PlanAndPlacePurchaseOrdersInterface
{
    /**
     * @param PlanAndPlacePurchaseOrdersInput $input
     * @return PlanAndPlacePurchaseOrdersResult
     */
    public function execute(PlanAndPlacePurchaseOrdersInput $input): PlanAndPlacePurchaseOrdersResult;
}
