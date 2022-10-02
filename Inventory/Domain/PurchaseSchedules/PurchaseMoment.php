<?php

namespace App\Inventory\Domain\PurchaseSchedules;

use App\SharedKernel\CleanArchitecture\Entity;
use Carbon\CarbonImmutable;

class PurchaseMoment extends Entity
{
    public const SUNDAY = 0;
    public const MONDAY = 1;
    public const TUESDAY = 2;
    public const WEDNESDAY = 3;
    public const THURSDAY =  4;
    public const FRIDAY = 5;
    public const SATURDAY = 6;
    public const DAY_OF_WEEK_MAPPING = [
        self::SUNDAY => 'sunday',
        self::MONDAY => 'monday',
        self::TUESDAY => 'tuesday',
        self::WEDNESDAY => 'wednesday',
        self::THURSDAY => 'thursday',
        self::FRIDAY => 'friday',
        self::SATURDAY => 'saturday',
    ];

    protected int $hour;
    protected int $minute;
    protected int $second;

    protected string $dayOfWeek;
    protected string $deliveryDayOfWeek;

    /**
     * @param int $hour
     * @param int $minute
     * @param string $dayOfWeek
     */
    public function __construct(int $hour, int $minute, string $dayOfWeek)
    {
        $this->hour = $hour;
        $this->minute = $minute;
        $this->dayOfWeek = $dayOfWeek;
    }

    public function hour(): int
    {
        return $this->hour;
    }

    public function minute(): int
    {
        return $this->minute;
    }

    public function isBefore(int $hour, int $minute): bool
    {
        if ($this->hour < $hour)
        {
            return true;
        }

        return $this->minute < $minute;
    }

    public function isAfter(int $hour, int $minute): bool
    {
        if ($this->hour > $hour)
        {
            return true;
        }

        return $this->minute > $minute;
    }

    public function dayOfWeek(): int
    {
        return $this->dayOfWeek;
    }

    public function dayOfWeekAsString(): string
    {
        return self::DAY_OF_WEEK_MAPPING[$this->dayOfWeek];
    }

    public function toDate(CarbonImmutable $date): CarbonImmutable
    {
        return $date->setHour($this->hour())->setMinute($this->minute());
    }

    protected function cascadeSetIdentity(int|string $id): void
    {
        // Nothing to do
    }
}
