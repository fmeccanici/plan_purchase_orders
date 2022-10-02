<?php

namespace App\Inventory\Infrastructure\Persistence\Eloquent\PurchaseSchedules;

use App\Inventory\Domain\PurchaseSchedules\PurchaseSchedule;
use App\Inventory\Infrastructure\Persistence\EntityMapperTrait;
use App\Inventory\Infrastructure\Persistence\ModelMapperTrait;
use App\Inventory\Infrastructure\Persistence\ReflectionClassCache;
use Exception;
use ReflectionException;

class EloquentPurchaseScheduleMapper extends PurchaseSchedule
{
    use EntityMapperTrait;
    use ModelMapperTrait;

    /**
     * @param EloquentPurchaseSchedule $model
     * @return PurchaseSchedule
     * @throws ReflectionException|Exception
     */
    protected static function reconstituteEntityCore(EloquentPurchaseSchedule $model): PurchaseSchedule
    {
        $orderClass = ReflectionClassCache::getReflectionClass(PurchaseSchedule::class);
        /** @var PurchaseSchedule $entity */
        $entity = $orderClass->newInstanceWithoutConstructor();

        $entity->id = $model->id;
        $entity->supplierId = $model->supplier_id;
        $entity->tag = $model->tag;

        // reconstituteEntity hasOne's

        // reconstituteEntities hasMany's
        $entity->purchaseMoments = EloquentPurchaseMomentMapper::reconstituteEntities($model->purchaseMoments);

        return $entity;
    }

    /**
     * @param PurchaseSchedule $entity
     * @return void
     * @throws Exception
     */
    protected static function createModelCore(PurchaseSchedule $entity): void
    {
        $model = new EloquentPurchaseSchedule();

        $model->id = $entity->id;
        $model->tag = $entity->tag;
        $model->supplier_id = $entity->supplierId;

        $model->save();
        $entity->setIdentity($model->id);

        // mapToModel hasOne's

        // mapToModels hasMany's
        EloquentPurchaseMomentMapper::createModels($entity->purchaseMoments);

    }

    /**
     * @param PurchaseSchedule $entity
     * @param EloquentPurchaseSchedule $model
     * @return void
     * @throws Exception
     */
    protected static function updateModelCore(PurchaseSchedule $entity, EloquentPurchaseSchedule $model): void
    {
        $model->id = $entity->id;
        $model->tag = $entity->tag;
        $model->supplier_id = $entity->supplierId;

        $model->save();
        $entity->setIdentity($model->id);

        // createOrUpdateModel hasOne's

        // createOrUpdateModels hasMany's
        EloquentPurchaseMomentMapper::createOrUpdateModels($entity->purchaseMoments, $model->purchaseMoments);
    }

    /**
     * @param EloquentPurchaseSchedule $model
     * @return void
     * @throws Exception
     */
    protected static function deleteModelCore(EloquentPurchaseSchedule $model): void
    {
        // purgeModel hasOne's

        // purgeModels hasMany's
        EloquentPurchaseMomentMapper::deleteModels($model->purchaseMoments);

        $model->delete();
    }

    /**
     * @param PurchaseSchedule $entity
     * @param EloquentPurchaseSchedule $model
     * @return void
     * @throws Exception
     */
    protected static function pruneModelCore(PurchaseSchedule $entity, EloquentPurchaseSchedule $model): void
    {
        // pruneModel hasOne's

        // pruneModel hasMany's
        EloquentPurchaseMomentMapper::pruneModels($entity->purchaseMoments, $model->purchaseMoments);
    }
}
