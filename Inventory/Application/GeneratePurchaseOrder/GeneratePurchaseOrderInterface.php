<?php


namespace App\Inventory\Application\GeneratePurchaseOrder;


interface GeneratePurchaseOrderInterface
{
    /**
     * @param GeneratePurchaseOrderInput $input
     * @return GeneratePurchaseOrderResult
     */
    public function execute(GeneratePurchaseOrderInput $input): GeneratePurchaseOrderResult;
}
