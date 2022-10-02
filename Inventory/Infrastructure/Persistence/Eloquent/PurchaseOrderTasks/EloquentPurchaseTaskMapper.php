<?php

namespace App\Inventory\Infrastructure\Persistence\Eloquent\PurchaseOrderTasks;

use App\Inventory\Domain\PurchaseTasks\PurchaseTask;
use App\Inventory\Infrastructure\Persistence\EntityMapperTrait;
use App\Inventory\Infrastructure\Persistence\ModelMapperTrait;
use App\Inventory\Infrastructure\Persistence\ReflectionClassCache;
use Carbon\CarbonImmutable;
use Exception;
use ReflectionException;

class EloquentPurchaseTaskMapper extends PurchaseTask
{
    use EntityMapperTrait;
    use ModelMapperTrait;

    /**
     * @param EloquentPurchaseTask $model
     * @return PurchaseTask
     * @throws ReflectionException|Exception
     */
    protected static function reconstituteEntityCore(EloquentPurchaseTask $model): PurchaseTask
    {
        $purchaseOrderPlacementTimeClass = ReflectionClassCache::getReflectionClass(PurchaseTask::class);

        /** @var PurchaseTask $entity */
        $entity = $purchaseOrderPlacementTimeClass->newInstanceWithoutConstructor();

        $entity->id = $model->id;
        $entity->purchaseScheduleId = (string) $model->purchase_schedule_id;
        $entity->plannedAt = CarbonImmutable::parse($model->planned_at);

        // reconstituteEntity hasOne's

        // reconstituteEntities hasMany's

        return $entity;
    }

    /**
     * @param PurchaseTask $entity
     * @return void
     * @throws Exception
     */
    protected static function createModelCore(PurchaseTask $entity): void
    {
        $model = new EloquentPurchaseTask();

        $model->id = $entity->id;
        $model->purchase_schedule_id = $entity->purchaseScheduleId;
        $model->planned_at = $entity->plannedAt->toDateTimeString();

        $model->save();
        $entity->setIdentity($model->id);

        // mapToModel hasOne's

        // mapToModels hasMany's
    }

    /**
     * @param PurchaseTask $entity
     * @param EloquentPurchaseTask $model
     * @return void
     * @throws Exception
     */
    protected static function updateModelCore(PurchaseTask $entity, EloquentPurchaseTask $model): void
    {
        $model->id = $entity->id;
        $model->purchase_schedule_id = $entity->purchaseScheduleId;
        $model->planned_at = $entity->plannedAt->toDateTimeString();

        $model->save();
        $entity->setIdentity($model->id);

        // createOrUpdateModel hasOne's

        // createOrUpdateModels hasMany's
    }

    /**
     * @param EloquentPurchaseTask $model
     * @return void
     * @throws Exception
     */
    protected static function deleteModelCore(EloquentPurchaseTask $model): void
    {
        // purgeModel hasOne's

        // purgeModels hasMany's

        $model->delete();
    }

    /**
     * @param PurchaseTask $entity
     * @param EloquentPurchaseTask $model
     * @return void
     * @throws Exception
     */
    protected static function pruneModelCore(PurchaseTask $entity, EloquentPurchaseTask $model): void
    {
        // pruneModel hasOne's

        // pruneModel hasMany's
    }
}
