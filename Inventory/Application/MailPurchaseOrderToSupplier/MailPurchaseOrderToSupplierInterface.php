<?php


namespace App\Inventory\Application\MailPurchaseOrderToSupplier;


interface MailPurchaseOrderToSupplierInterface
{
    /**
     * @param MailPurchaseOrderToSupplierInput $input
     * @return MailPurchaseOrderToSupplierResult
     */
    public function execute(MailPurchaseOrderToSupplierInput $input): MailPurchaseOrderToSupplierResult;
}
