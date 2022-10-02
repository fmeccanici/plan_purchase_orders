<?php

namespace App\Inventory\Infrastructure\Persistence\Eloquent\PurchaseSchedules;

use App\Inventory\Domain\PurchaseSchedules\PurchaseMoment;
use App\Inventory\Infrastructure\Persistence\EntityMapperTrait;
use App\Inventory\Infrastructure\Persistence\ModelMapperTrait;
use App\Inventory\Infrastructure\Persistence\ReflectionClassCache;
use Exception;
use ReflectionException;

class EloquentPurchaseMomentMapper extends PurchaseMoment
{
    use EntityMapperTrait;
    use ModelMapperTrait;

    /**
     * @param EloquentPurchaseMoment $model
     * @return PurchaseMoment
     * @throws ReflectionException|Exception
     */
    protected static function reconstituteEntityCore(EloquentPurchaseMoment $model): PurchaseMoment
    {
        $orderClass = ReflectionClassCache::getReflectionClass(PurchaseMoment::class);
        /** @var PurchaseMoment $entity */
        $entity = $orderClass->newInstanceWithoutConstructor();

        $entity->id = $model->id;
        $entity->parentId = (string) $model->purchase_schedule_id;
        $entity->dayOfWeek = $model->day_of_week;
        $entity->hour = $model->hour;
        $entity->minute = $model->minute;

        // reconstituteEntity hasOne's

        // reconstituteEntities hasMany's

        return $entity;
    }

    /**
     * @param PurchaseMoment $entity
     * @return void
     * @throws Exception
     */
    protected static function createModelCore(PurchaseMoment $entity): void
    {
        $model = new EloquentPurchaseMoment();

        $model->id = $entity->id;
        $model->purchase_schedule_id = $entity->parentId;
        $model->day_of_week = $entity->dayOfWeek;
        $model->hour = $entity->hour;
        $model->minute = $entity->minute;

        $model->save();
        $entity->setIdentity($model->id);

        // mapToModel hasOne's

        // mapToModels hasMany's

    }

    /**
     * @param PurchaseMoment $entity
     * @param EloquentPurchaseMoment $model
     * @return void
     * @throws Exception
     */
    protected static function updateModelCore(PurchaseMoment $entity, EloquentPurchaseMoment $model): void
    {
        $model->id = $entity->id;
        $model->purchase_schedule_id = $entity->parentId;
        $model->day_of_week = $entity->dayOfWeek;
        $model->hour = $entity->hour;
        $model->minute = $entity->minute;

        $model->save();
        $entity->setIdentity($model->id);

        // createOrUpdateModel hasOne's

        // createOrUpdateModels hasMany's

    }

    /**
     * @param EloquentPurchaseMoment $model
     * @return void
     * @throws Exception
     */
    protected static function deleteModelCore(EloquentPurchaseMoment $model): void
    {
        // purgeModel hasOne's

        // purgeModels hasMany's

        $model->delete();
    }

    /**
     * @param PurchaseMoment $entity
     * @param EloquentPurchaseMoment $model
     * @return void
     * @throws Exception
     */
    protected static function pruneModelCore(PurchaseMoment $entity, EloquentPurchaseMoment $model): void
    {
        // pruneModel hasOne's

        // pruneModel hasMany's
    }
}
