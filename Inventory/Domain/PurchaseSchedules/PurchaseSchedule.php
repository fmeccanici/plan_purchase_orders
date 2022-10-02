<?php

namespace App\Inventory\Domain\PurchaseSchedules;

use App\Inventory\Domain\Exceptions\PurchaseScheduleOperationException;
use App\SharedKernel\CleanArchitecture\AggregateRoot;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PurchaseSchedule extends AggregateRoot implements Arrayable
{
    protected string $supplierId;
    protected string $tag;
    protected Collection $purchaseMoments;

    /**
     * @param string $supplierId
     * @param string $tag
     * @param Collection $purchaseMoments
     */
    public function __construct(string $supplierId, string $tag, Collection $purchaseMoments)
    {
        $this->supplierId = $supplierId;
        $this->tag = $tag;
        $this->purchaseMoments = $purchaseMoments;
    }

    public function purchaseMoments(): Collection
    {
        return $this->purchaseMoments;
    }

    public function monday(): Collection
    {
        return $this->purchaseMomentsAt(PurchaseMoment::MONDAY);
    }

    public function tuesday(): Collection
    {
        return $this->purchaseMomentsAt(PurchaseMoment::TUESDAY);
    }

    public function wednesday(): Collection
    {
        return $this->purchaseMomentsAt(PurchaseMoment::WEDNESDAY);
    }

    public function thursday(): Collection
    {
        return $this->purchaseMomentsAt(PurchaseMoment::THURSDAY);
    }

    public function friday(): Collection
    {
        return $this->purchaseMomentsAt(PurchaseMoment::FRIDAY);
    }

    public function saturday(): Collection
    {
        return $this->purchaseMomentsAt(PurchaseMoment::SATURDAY);
    }

    public function sunday(): Collection
    {
        return $this->purchaseMomentsAt(PurchaseMoment::SUNDAY);
    }

    private function purchaseMomentsAt(int $dayOfWeek): Collection
    {
        return $this->purchaseMoments->filter(function (PurchaseMoment $purchaseOrderPlacementDay) use ($dayOfWeek) {
            return $purchaseOrderPlacementDay->dayOfWeek() === $dayOfWeek;
        });
    }

    public function nextPurchaseMomentOfTheDay(?CarbonImmutable $from = null): ?PurchaseMoment
    {
        if ($from === null)
        {
            $purchaseDay = CarbonImmutable::now();
        } else {
            $purchaseDay = $from;
        }

        $purchaseDayName = Str::lower($purchaseDay->format('l'));
        $purchaseHour = (int) $purchaseDay->format('H');
        $purchaseMinute = (int) $purchaseDay->format('i');

        $purchaseMoments = $this->{$purchaseDayName}();

        return $purchaseMoments->filter(function (PurchaseMoment $purchaseMoment) use ($purchaseHour, $purchaseMinute) {
            return $purchaseMoment->isAfter($purchaseHour, $purchaseMinute);
        })?->sort(function (PurchaseMoment $purchaseMomentA, PurchaseMoment $purchaseMomentB) {
            if ($purchaseMomentA->isBefore($purchaseMomentB->hour(), $purchaseMomentB->minute()))
            {
                return -1;
            } else if ($purchaseMomentA->isAfter($purchaseMomentB->hour(), $purchaseMomentB->minute()))
            {
                return 1;
            }

            return 0;
        })->first();
    }

    /**
     * @throws PurchaseScheduleOperationException
     */
    public function deliveryDate(int $dayOfWeek): CarbonImmutable
    {
        $purchaseMoment = $this->purchaseMomentsAt($dayOfWeek)->first();

        if ($purchaseMoment === null)
        {
            $dayOfWeekAsString = PurchaseMoment::DAY_OF_WEEK_MAPPING[$dayOfWeek];
            throw new PurchaseScheduleOperationException('There are no purchase moments on ' . $dayOfWeekAsString);
        }

        $deliveryDayOfWeek = $purchaseMoment->deliveryDayOfWeek();

        return CarbonImmutable::now()->next($deliveryDayOfWeek);
    }

    public function supplierId(): string
    {
        return $this->supplierId;
    }

    public function tag(): string
    {
        return $this->tag;
    }

    public function toArray()
    {
        // TODO: Implement toArray() method.
    }

    protected function cascadeSetIdentity(int|string $id): void
    {
        $this->purchaseMoments->each(fn(PurchaseMoment $x) => $x->setParentIdentity($id));
    }
}
