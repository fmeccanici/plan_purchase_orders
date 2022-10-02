<?php

namespace App\Inventory\Domain\PurchaseOrders;

use Illuminate\Support\Arr;

class CreatePurchaseOrderTemplateSettings
{
    public static function fromArray(array $settings): PurchaseOrderTemplateSettings
    {
        $productCodeSupplierColumnName = Arr::get($settings, 'product_code_supplier');
        $productCodeColumnName = Arr::get($settings, 'product_code');
        $productNameColumnName = Arr::get($settings, 'product_name');
        $quantityColumnName = Arr::get($settings, 'quantity');
        $deliveryWorkDaysColumnName = Arr::get($settings, 'delivery_work_days');

        $includedColumns = array();
        $columnNames = array();

        if ($productCodeSupplierColumnName !== null)
        {
            $columnNames['product_code_supplier'] = $productCodeSupplierColumnName;
            $includedColumns[] = 'product_code_supplier';
        }

        if ($productCodeColumnName !== null)
        {
            $columnNames['product_code'] = $productCodeColumnName;
            $includedColumns[] = 'product_code';
        }

        if ($productNameColumnName !== null)
        {
            $columnNames['product_name'] = $productNameColumnName;
            $includedColumns[] = 'product_name';
        }

        if ($quantityColumnName !== null)
        {
            $columnNames['quantity'] = $quantityColumnName;
            $includedColumns[] = 'quantity';
        }

        if ($deliveryWorkDaysColumnName !== null)
        {
            $columnNames['delivery_work_days'] = $deliveryWorkDaysColumnName;
            $includedColumns[] = 'delivery_work_days';
        }

        return new PurchaseOrderTemplateSettings(collect($includedColumns), collect($columnNames));
    }
}
