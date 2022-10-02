<?php

namespace App\Inventory\Infrastructure\Persistence\Eloquent\PurchaseSchedules;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string|int $id
 * @property string $tag
 * @property string $supplier_id
 */
class EloquentPurchaseSchedule extends Model
{
    protected $table = 'purchase_schedules';

    public function purchaseMoments()
    {
        return $this->hasMany(EloquentPurchaseMoment::class, 'purchase_schedule_id', 'id');
    }
}
