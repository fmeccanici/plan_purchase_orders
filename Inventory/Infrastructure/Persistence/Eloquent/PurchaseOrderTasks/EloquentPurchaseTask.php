<?php

namespace App\Inventory\Infrastructure\Persistence\Eloquent\PurchaseOrderTasks;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $planned_at
 * @property int $purchase_schedule_id
 */
class EloquentPurchaseTask extends Model
{
    protected $table = 'purchase_tasks';
}
