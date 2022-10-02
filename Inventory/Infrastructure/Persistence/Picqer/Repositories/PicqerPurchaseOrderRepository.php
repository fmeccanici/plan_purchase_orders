<?php

namespace App\Inventory\Infrastructure\Persistence\Picqer\Repositories;

use App\Inventory\Domain\Exceptions\ProductNotFoundException;
use App\Inventory\Domain\PurchaseOrders\PurchaseOrder;
use App\Inventory\Domain\PurchaseOrders\PurchaseOrderLine;
use App\Inventory\Domain\PurchaseOrders\PurchaseOrderStatus;
use App\Inventory\Infrastructure\ApiClients\PicqerApiClient;
use App\Inventory\Infrastructure\Exceptions\PicqerInventoryItemRepositoryException;
use App\Inventory\Infrastructure\Exceptions\PicqerPurchaseOrderRepositoryOperationException;
use Illuminate\Support\Arr;
use Picqer\Api\Client;

class PicqerPurchaseOrderRepository implements \App\Inventory\Domain\Repositories\PurchaseOrderRepositoryInterface
{
    private Client $apiClient;

    public function __construct(PicqerApiClient $apiClient)
    {
        $this->apiClient = $apiClient->getClient();
    }

    public function findOneById(string $id): ?PurchaseOrder
    {
        $getPurchaseOrderResponse = $this->apiClient->getPurchaseorder($id);

        if (! Arr::get($getPurchaseOrderResponse, 'success'))
        {
            return null;
        }

        $products = collect(Arr::get($getPurchaseOrderResponse, 'products'));
        $purchaseOrderLines = $products->map(function (array $product) {
            $productCode = Arr::get($product, 'productcode');
            $productCodeSupplier = Arr::get($product, 'productcode_supplier');
            $productName = Arr::get($product, 'name');
            $quantity = Arr::get($product, 'amount');
            $price = Arr::get($product, 'price');
            $deliveryWorkDays = Arr::get($product, 'deliverytime');
            $idProduct = Arr::get($product, 'idproduct');

            $getProductResponse = $this->apiClient->getProduct($idProduct);

            if (! Arr::get($getProductResponse, 'success'))
            {
                return null;
            }

            return new PurchaseOrderLine($productCode, $productCodeSupplier, $productName, $quantity, $price, $deliveryWorkDays);
        });

        $picqerPurchaseOrder = Arr::get($getPurchaseOrderResponse, 'data');
        $supplierId = Arr::get($picqerPurchaseOrder, 'idsupplier');

        $status = Arr::get($picqerPurchaseOrder, 'status');
        $remarks = Arr::get($picqerPurchaseOrder, 'remarks');
        $reference = Arr::get($picqerPurchaseOrder, 'purchaseorderid');

        return new PurchaseOrder($purchaseOrderLines, $supplierId, new PurchaseOrderStatus($status), $remarks, $reference);
    }

    /**
     * @throws PicqerPurchaseOrderRepositoryOperationException
     * @throws PicqerInventoryItemRepositoryException
     */
    public function save(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        if ($purchaseOrder->identity() == null)
        {
            return $this->create($purchaseOrder);
        }

        $existingPurchaseOrder = $this->findOneById($purchaseOrder->identity());

        if ($existingPurchaseOrder == null)
        {
            return $this->create($purchaseOrder);
        } else {
            return $this->update($purchaseOrder);
        }
    }

    /**
     * @throws PicqerPurchaseOrderRepositoryOperationException
     * @throws ProductNotFoundException
     */
    private function create(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        $products = [];
        foreach ($purchaseOrder->purchaseOrderLines() as $purchaseOrderLine)
        {
            $getProductResponse = $this->apiClient->getProducts([
                'productcode' => $purchaseOrderLine->productCode()
            ]);

            if (! Arr::get($getProductResponse, 'success'))
            {
                throw new PicqerPurchaseOrderRepositoryOperationException('Failed getting product with product code ' . $purchaseOrderLine->productCode() . ', with error ' . Arr::get($getProductResponse, 'errormessage'));
            }

            $picqerProduct = Arr::get($getProductResponse, 'data');

            if (sizeof($picqerProduct) === 0)
            {
                throw new ProductNotFoundException('Failed getting product with product code ' . $purchaseOrderLine->productCode() . ' using getProducts from picqer: it returns an empty array');
            }

            $products[] = [
                'idproduct' => Arr::get($picqerProduct[0], 'idproduct'),
                'price' => Arr::get($picqerProduct[0], 'fixedstockprice'),
                'amount' => $purchaseOrderLine->quantity(),
                'delivery_date' => $purchaseOrderLine->deliveryDate()?->format(config('picqer.datetime_format'))
            ];
        }

        $picqerPurchaseOrder = [
            'idsupplier' => $purchaseOrder->supplierId(),
            'products' => $products,
            'remarks' => $purchaseOrder->remarks(),
            'delivery_date' => $purchaseOrder->deliveryDate()?->format(config('picqer.datetime_format'))
        ];

        $addPurchaseOrderResponse = $this->apiClient->addPurchaseorder($picqerPurchaseOrder);

        if (! Arr::get($addPurchaseOrderResponse, 'success'))
        {
            throw new PicqerPurchaseOrderRepositoryOperationException('Failed adding purchase order with error: ' . Arr::get($addPurchaseOrderResponse, 'errormessage'));
        }

        $piqcerPurchaseOrder = Arr::get($addPurchaseOrderResponse, 'data');
        $purchaseOrder->setIdentity(Arr::get($piqcerPurchaseOrder, 'idpurchaseorder'));
        $purchaseOrder->setReference(Arr::get($piqcerPurchaseOrder, 'purchaseorderid'));

        $idPurchaseOrder = Arr::get($addPurchaseOrderResponse, 'data.idpurchaseorder');
        $getPurchaseOrder = $this->apiClient->getPurchaseorder($idPurchaseOrder);

        if (! Arr::get($getPurchaseOrder, 'success'))
        {
            throw new PicqerPurchaseOrderRepositoryOperationException('Failed getting purchase order with id ' . $idPurchaseOrder . ', with error ' . Arr::get($getProductResponse, 'errormessage'));
        }

        $picqerPurchaseOrder = Arr::get($getPurchaseOrder, 'data');

        $picqerProducts = collect(Arr::get($picqerPurchaseOrder, 'products'));

        $picqerProducts->each(function (array $picqerProduct) use ($products, $idPurchaseOrder) {

            foreach ($products as $product)
            {
                if (Arr::get($product, 'idproduct') == Arr::get($picqerProduct, 'idproduct'))
                {
                    $idPurchaseOrderProduct = Arr::get($picqerProduct, 'idpurchaseorder_product');
                    $this->apiClient->updatePurchaseorderProduct($idPurchaseOrder, $idPurchaseOrderProduct, $product);
                }
            }
        });

        return $purchaseOrder;
    }

    /**
     * @throws PicqerPurchaseOrderRepositoryOperationException
     */
    private function update(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        $markPurchaseOrderAsPurchasedResponse = $this->apiClient->markPurchaseorderAsPurchased($purchaseOrder->identity());

        if (! Arr::get($markPurchaseOrderAsPurchasedResponse, 'success'))
        {
            throw new PicqerPurchaseOrderRepositoryOperationException('Failed marking purchase order as purchased with error: ' . Arr::get($markPurchaseOrderAsPurchasedResponse, 'errormessage'));
        }

        return $purchaseOrder;
    }
}
