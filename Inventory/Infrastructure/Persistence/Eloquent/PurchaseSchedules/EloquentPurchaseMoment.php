<?php

namespace App\Inventory\Infrastructure\Persistence\Eloquent\PurchaseSchedules;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string|int $id
 * @property string|int $purchase_schedule_id
 * @property string $day_of_week
 * @property int $hour
 * @property int $minute
 */
class EloquentPurchaseMoment extends Model
{
    protected $table = "purchase_moments";

}
