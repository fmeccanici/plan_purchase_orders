<?php

namespace App\Inventory\Domain\PurchaseOrders;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelPurchaseOrderTemplate extends PurchaseOrderTemplate
{

    protected Xlsx $writer;

    function export(PurchaseOrderTemplateSettings $purchaseOrderTemplateSettings): PurchaseOrderExport
    {
        $spreadSheet = new Spreadsheet();
        $sheet = $spreadSheet->getSheet(0);

        $j = 1;
        foreach ($purchaseOrderTemplateSettings->columnNames()->toArray() as $columnName)
        {
            $sheet->setCellValueByColumnAndRow($j, 1, $columnName);
            $j++;
        }

        $purchaseOrderReference = $this->purchaseOrder->reference();
        $fileName = $purchaseOrderReference . '.xlsx';
        $folder = 'purchase_orders';
        $filePath = $folder . '/' . $fileName;

        $purchaseOrderLines = $this->purchaseOrder->purchaseOrderLines();

        $i = 0;
        $purchaseOrderLines->each(function (PurchaseOrderLine $purchaseOrderLine) use ($purchaseOrderTemplateSettings, &$sheet, &$i) {
            $j = 1;

            if ($purchaseOrderTemplateSettings->columnIncluded('product_code_supplier'))
            {
                $productCodeSupplier = $purchaseOrderLine->productCodeSupplier();
                $sheet->setCellValueExplicitByColumnAndRow($j, $i+2, $productCodeSupplier, DataType::TYPE_STRING2);
                $j++;
            }

            if ($purchaseOrderTemplateSettings->columnIncluded('product_code'))
            {
                $productCode = $purchaseOrderLine->productCode();
                $sheet->setCellValueExplicitByColumnAndRow($j, $i+2, $productCode, DataType::TYPE_STRING2);
                $j++;
            }

            if ($purchaseOrderTemplateSettings->columnIncluded('product_name'))
            {
                $productCode = $purchaseOrderLine->productName();
                $sheet->setCellValueExplicitByColumnAndRow($j, $i+2, $productCode, DataType::TYPE_STRING2);
                $j++;
            }

            if ($purchaseOrderTemplateSettings->columnIncluded('quantity'))
            {
                $quantity = $purchaseOrderLine->quantity();
                $sheet->setCellValueExplicitByColumnAndRow($j, $i+2, $quantity, DataType::TYPE_NUMERIC);
                $j++;
            }

            if ($purchaseOrderTemplateSettings->columnIncluded('delivery_work_days'))
            {
                $deliveryWorkDays = $purchaseOrderLine->deliveryWorkDays();
                $sheet->setCellValueExplicitByColumnAndRow($j, $i+2, $deliveryWorkDays, DataType::TYPE_NUMERIC);
            }

            $i++;

        });

        // Create Styles Array
        $styleArrayFirstRow = [
            'font' => [
                'bold' => true,
            ]
        ];

        // Retrieve Highest Column (e.g AE)
        $highestColumn = $sheet->getHighestColumn();

        // Set first row bold
        $sheet->getStyle('A1:' . $highestColumn . '1' )->applyFromArray($styleArrayFirstRow);

        $this->writer = new Xlsx($spreadSheet);

        if (! Storage::exists($folder))
        {
            Storage::makeDirectory($folder);
        }

        $path = Storage::path($filePath);

        $this->writer->save($path);

        $url = URL::signedRoute('stream-purchase-order', [
            'purchaseOrderReference' => $purchaseOrderReference
        ]);

        $purchaseOrderExport = new PurchaseOrderExport($path, $url);
        $purchaseOrderExport->setIdentity($purchaseOrderReference);
        return $purchaseOrderExport;
    }
}
