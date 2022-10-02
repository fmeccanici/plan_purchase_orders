<?php

namespace App\Inventory\Domain\PurchaseTasks;

use App\SharedKernel\CleanArchitecture\AggregateRoot;
use Carbon\CarbonImmutable;

class PurchaseTask extends AggregateRoot
{
    protected string $tag;
    protected string|int $purchaseScheduleId;
    protected CarbonImmutable $plannedAt;

    /**
     * @param int|string $purchaseScheduleId
     * @param CarbonImmutable $plannedAt
     */
    public function __construct(int|string $purchaseScheduleId, CarbonImmutable $plannedAt)
    {
        $this->purchaseScheduleId = $purchaseScheduleId;
        $this->plannedAt = $plannedAt;
    }

    public function purchaseScheduleId(): string|int
    {
        return $this->purchaseScheduleId;
    }

    public function tag(): string
    {
        return $this->tag;
    }

    public function plannedAt(): CarbonImmutable
    {
        return $this->plannedAt;
    }

    protected function cascadeSetIdentity(int|string $id): void
    {
        // Nothing to do
    }
}
