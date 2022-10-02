<?php


namespace App\Inventory\Application\PlanAndPlacePurchaseOrders;

use App\Inventory\Domain\PurchaseSchedules\PurchaseSchedule;
use App\Inventory\Domain\PurchaseTasks\PurchaseTask;
use App\Inventory\Domain\Repositories\PurchaseScheduleRepositoryInterface;
use App\Inventory\Domain\Repositories\PurchaseTaskRepositoryInterface;
use App\Inventory\Domain\Repositories\SupplierRepositoryInterface;
use App\Inventory\Domain\Services\PurchaseTaskExecutionServiceInterface;
use App\Inventory\Domain\Suppliers\Supplier;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class PlanAndPlacePurchaseOrders implements PlanAndPlacePurchaseOrdersInterface
{
    protected SupplierRepositoryInterface $supplierRepository;
    protected PurchaseScheduleRepositoryInterface $purchaseScheduleRepository;
    protected PurchaseTaskExecutionServiceInterface $purchaseTaskExecutionService;
    protected PurchaseTaskRepositoryInterface $purchaseTaskRepository;

    /**
     * PlanPurchaseOrderPlacements constructor.
     */
    public function __construct(SupplierRepositoryInterface           $supplierRepository,
                                PurchaseScheduleRepositoryInterface   $purchaseScheduleRepository,
                                PurchaseTaskExecutionServiceInterface $purchaseTaskExecutionService,
                                PurchaseTaskRepositoryInterface       $purchaseTaskRepository)
    {
        $this->supplierRepository = $supplierRepository;
        $this->purchaseScheduleRepository = $purchaseScheduleRepository;
        $this->purchaseTaskExecutionService = $purchaseTaskExecutionService;
        $this->purchaseTaskRepository = $purchaseTaskRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute(PlanAndPlacePurchaseOrdersInput $input): PlanAndPlacePurchaseOrdersResult
    {
        $suppliers = $this->fetchSuppliers($input);
        $suppliers->each(function (Supplier $supplier) {
            $tags = $supplier->tags();
            $supplierId = $supplier->identity();

            $tags->each(function (string $tag) use ($supplierId) {
                $purchaseSchedule = $this->purchaseScheduleRepository->findOneBySupplierIdAndTag($supplierId, $tag);

                if ($purchaseSchedule === null)
                {
                    Log::error('Purchase schedule for supplier id ' . $supplierId . ' and tag ' . $tag . ' not found');
                    return;
                }

                $purchaseTask = $this->purchaseTaskRepository->findOneByPurchaseScheduleId($purchaseSchedule->identity());

                if ($purchaseTask === null)
                {
                    $purchaseTask = $this->planPurchaseTaskOnNextPurchaseMoment($purchaseSchedule);
                }

                if ($purchaseTask !== null)
                {
                    $now = CarbonImmutable::now();

                    if ($now->isSameDay($purchaseTask->plannedAt()))
                    {
                        if ($now->equalTo($purchaseTask->plannedAt()) || $now->isAfter($purchaseTask->plannedAt()))
                        {
                            Log::notice(sprintf('Executing task planned at %s for supplier with id %s and tag %s', $purchaseTask->plannedAt()->toDateTimeString(), $supplierId, $tag));

                            $this->purchaseTaskExecutionService->execute($purchaseTask);
                            $this->purchaseTaskRepository->delete($purchaseTask);
                        }
                    } else {
                        $this->purchaseTaskRepository->delete($purchaseTask);
                    }
                }
            });
        });

        return new PlanAndPlacePurchaseOrdersResult();
    }

    private function fetchSuppliers(PlanAndPlacePurchaseOrdersInput $input): Collection
    {
        if ($input->allSuppliersAndTags())
        {
            return $this->supplierRepository->findAll();
        } else {
            return collect($input->suppliers())->map(function (array $supplierArray) {
                $supplierId = Arr::get($supplierArray, 'id');
                $supplier = $this->supplierRepository->findOneById($supplierId);

                if ($supplier === null)
                {
                    Log::error('Supplier with id ' . $supplierId);
                } else {
                    $supplier->setTags(collect(Arr::get($supplierArray, 'tags')));
                }

                return $supplier;
            })->filter();
        }
    }

    /**
     * @param PurchaseSchedule $purchaseSchedule
     * @return PurchaseTask|null
     */
    function planPurchaseTaskOnNextPurchaseMoment(PurchaseSchedule $purchaseSchedule): ?PurchaseTask
    {
        $nextPurchaseMoment = $purchaseSchedule->nextPurchaseMomentOfTheDay();

        if ($nextPurchaseMoment !== null) {
            $purchaseTask = new PurchaseTask($purchaseSchedule->identity(), $nextPurchaseMoment->toDate(CarbonImmutable::now()));

            return $this->purchaseTaskRepository->save($purchaseTask);
        }

        return null;
    }

}
