<?php


namespace App\Inventory\Infrastructure\Persistence\Eloquent\Repositories;


use App\Inventory\Domain\InventoryItems\InventoryItem;
use App\Inventory\Domain\InventoryItems\InventoryItemAnalytics;
use App\Inventory\Domain\Repositories\DestinationInventoryItemRepositoryInterface;
use App\Inventory\Domain\Repositories\SourceInventoryItemRepositoryInterface;
use App\Inventory\Infrastructure\Exceptions\EloquentInventoryItemOperationException;
use App\Inventory\Infrastructure\Persistence\Eloquent\InventoryItems\EloquentInventoryItem;
use App\Inventory\Infrastructure\Persistence\Eloquent\InventoryItems\EloquentInventoryItemMapper;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class EloquentInventoryItemRepository implements SourceInventoryItemRepositoryInterface, DestinationInventoryItemRepositoryInterface
{

    /**
     * @throws EloquentInventoryItemOperationException
     */
    public function list(array $listOptions = []): LengthAwarePaginator
    {
        $itemsPerPage = Arr::get($listOptions, 'pagingOptions.itemsPerPage', 50);
        $page = Arr::get($listOptions, 'pagingOptions.page', 1);
        $sortBy = Arr::get($listOptions, 'pagingOptions.sortBy', []);
        $sortDesc = Arr::get($listOptions, 'pagingOptions.sortDesc', []);
        $search = Arr::get($listOptions, 'pagingOptions.search');
        $filters = Arr::get($listOptions, 'pagingOptions.filters', []);

        if (! $page)
        {
            throw new EloquentInventoryItemOperationException("Page should be specified");
        }

        if (! $itemsPerPage)
        {
            throw new EloquentInventoryItemOperationException("Items per page should be specified");
        }

        $query = EloquentInventoryItem::query();

        // Sort
        $sortables = collect($sortBy)->mapWithKeys(function ($sortName, $key) use ($sortDesc) {
            return [$sortName => (bool) $sortDesc[$key]];
        });

        if($sortables->isNotEmpty()) {
            foreach ($sortables as $sortName => $desc)
            {
                $query->orderBy($sortName,  $desc ? 'desc' : 'asc');
            }
        } else {
            $query->latest();
        }

        // Search
        if($search) {
            $query->where(function($searchQuery) use ($search) {
                $searchQuery->where('description', 'like', '%'.$search.'%')
                    ->orWhere('id', 'like', '%'.$search.'%');
            });
        }

        // Filters
        if($filters) {
            if(!(in_array('sold', $filters, true) && in_array('in_stock', $filters, true))) {
                if(in_array('sold', $filters, true)) {
                    $query->whereNotNull('sold_at');
                } elseif(in_array('in_stock', $filters, true)) {
                    $query->whereNull('sold_at');
                }
            }

            // Type filter
            foreach (['doormat', 'squid'] as $typeFilter) {
                if(in_array($typeFilter, $filters, true)) {
                    $query->where('type', EloquentInventoryItem::$types[$typeFilter]);
                    break;
                }
            }
        }

        $eloquentInventoryItems = $query->get();
        $inventoryItems = EloquentInventoryItemMapper::toEntities($eloquentInventoryItems);

        $offset = ($page - 1) * $itemsPerPage;
        $inventoryItemsOfThisPage = $inventoryItems->slice($offset, $itemsPerPage)->values();

        return new LengthAwarePaginator($inventoryItemsOfThisPage, $inventoryItems->count(), $itemsPerPage, $page);
    }

    public function save(InventoryItem $inventoryItem): InventoryItem
    {
        $eloquentInventoryItem = EloquentInventoryItemMapper::toEloquent($inventoryItem);
        $eloquentInventoryItem->save();

        return EloquentInventoryItemMapper::toEntity(collect([$eloquentInventoryItem]));
    }

    public function saveMultiple(Collection $inventoryItems): void
    {
        $inventoryItems->each(function (InventoryItem $inventoryItem) {
            $eloquentInventoryItem = EloquentInventoryItemMapper::toEloquent($inventoryItem);
            $eloquentInventoryItem->push();
        });
    }

    public function findOneByRegistrationNumber($registrationNumber): ?InventoryItem
    {
        $eloquentInventoryItem = EloquentInventoryItem::query()->where('registration_number', $registrationNumber)->first();

        return EloquentInventoryItemMapper::toEntity(collect([$eloquentInventoryItem]));
    }

    public function findAllByRegistrationNumbers(array $registrationNumbers): Collection
    {
        return EloquentInventoryItemMapper::toEntities(EloquentInventoryItem::query()->whereIn('registration_number', $registrationNumbers)
                                                                            ->get());
    }

    public function removeByRegistrationNumbers(array $registrationNumbers): void
    {
        EloquentInventoryItem::destroy($registrationNumbers);
    }

    public function update(InventoryItem $inventoryItem): void
    {
         EloquentInventoryItem::query()
            ->where('registration_number', $inventoryItem->registrationNumber())
            ->update([
                'description' => $inventoryItem->description(),
                'brand' => $inventoryItem->brand(),
                'color' => $inventoryItem->color(),
                'width' => $inventoryItem->width()->quantity(),
                'width_measure' => $inventoryItem->width()->measure(),
                'length' => $inventoryItem->length()->quantity(),
                'length_measure' => $inventoryItem->length()->measure(),
                'height' => $inventoryItem->height()->quantity(),
                'height_measure' => $inventoryItem->height()->measure(),
                'location' => $inventoryItem->location(),
                'quantity' => $inventoryItem->stock()->free(),
                'employee_id' => $inventoryItem->employeeWhoAddedInventoryItem(),
                'sold_price' => $inventoryItem->soldPrice(),
                'sold_at' => $inventoryItem->soldAt()->format("Y-m-d H:i:s")
            ]);
    }

    public function findAll(bool $withDesiredStock = true): Collection
    {
        $eloquentInventoryItems = EloquentInventoryItem::all();
        return EloquentInventoryItemMapper::toEntities($eloquentInventoryItems);
    }

    public function findOneByProductCode(string $productCode): ?InventoryItem
    {
        $eloquentInventoryItem = EloquentInventoryItem::where('code', $productCode)->first();
        return EloquentInventoryItemMapper::toEntity(collect([$eloquentInventoryItem]));
    }

    public function delete(InventoryItem $inventoryItem): void
    {
        // TODO: Implement delete() method.
    }

    /**
     * @param string $entity
     * @param int $size
     * @param callable|null $callback
     * @return void
     */
    public function chunk(string $entity, int $size = 100, callable $callback = null): void
    {
        if(!is_callable($callback)) {
            return;
        }

        EloquentInventoryItem::query()->chunk($size, function($item) use ($callback) {
            $callback($item);
        });
    }

    public function findAllByType(string $type): Collection
    {
        // TODO: Implement findAllByType() method.
    }

    public function findAllByProductCode(string $productCode): Collection
    {
        $eloquentInventoryItems = EloquentInventoryItem::where('code', $productCode)->get();
        return EloquentInventoryItemMapper::toEntities($eloquentInventoryItems);
    }

    public function getAnalytics(CarbonImmutable $startRangeDate = null, CarbonImmutable $endRangeDate = null, string $type = null): ?InventoryItemAnalytics
    {

        // select count(id) as registered, date(created_at) as created_date from inventory_items where type = 2 group by date(created_at) order by date(created_at) desc;
        // select count(sold_at) as total_sold, sum(sold_price) as total_sold_price, date(sold_at) as sold_date from inventory_items where type = 1 group by sold_date order by sold_date desc;

        $registeredInventoryItems = EloquentInventoryItem::query()
            ->selectRaw('count(id) as registered, date(created_at) as created_date')
            ->when($startRangeDate, function(\Illuminate\Database\Eloquent\Builder $query) use ($startRangeDate) {
                $query->where('created_at', '>=', $startRangeDate->toMutable()->startOfDay());
            })
            ->when($endRangeDate, function(\Illuminate\Database\Eloquent\Builder $query) use ($endRangeDate) {
                $query->where('created_at', '<', $endRangeDate->toMutable()->endOfDay());
            })
            ->when($type, function(\Illuminate\Database\Eloquent\Builder $query) use ($type) {
                $query->where('type', EloquentInventoryItem::$types[$type]);
            })
            ->groupBy('created_date')
            ->get();

        $soldInventoryItems = EloquentInventoryItem::query()
            ->selectRaw('count(sold_at) as total_sold, sum(sold_price) as total_sold_price, date(sold_at) as sold_date')
            ->when($startRangeDate, function(\Illuminate\Database\Eloquent\Builder $query) use ($startRangeDate) {
                $query->where('sold_at', '>=', $startRangeDate->toMutable()->startOfDay());
            })
            ->when($endRangeDate, function(\Illuminate\Database\Eloquent\Builder $query) use ($endRangeDate) {
                $query->where('sold_at', '<', $endRangeDate->toMutable()->endOfDay());
            })
            ->when($type, function(\Illuminate\Database\Eloquent\Builder $query) use ($type) {
                $query->where('type', EloquentInventoryItem::$types[$type]);
            })
            ->whereNotNull('sold_at')
            ->groupBy('sold_date')
            ->get();

        $range = collect($startRangeDate->range($endRangeDate));
        $columns = collect(['Registraties', 'Conversies']);

        return new InventoryItemAnalytics(
            dates: $range->map->format('Y-m-d'),
            columns: $columns,
            values: $range->map(function(CarbonImmutable $date) use ($registeredInventoryItems, $soldInventoryItems) {

                $registeredItem = $registeredInventoryItems->first(function($item) use ($date) {
                    return $item->created_date === $date->format('Y-m-d');
                });

                $soldItem = $soldInventoryItems->first(function($item) use ($date) {
                    return $item->sold_date === $date->format('Y-m-d');
                });

                return [
                    optional($registeredItem)->registered ?? 0,
                    optional($soldItem)->total_sold ?? 0,
                ];
            })
        );
    }

    public function findAllAvailableByProductCode(string $productCode): Collection
    {
        $eloquentInventoryItems = EloquentInventoryItem::where('code', $productCode)->whereNull('sold_at')->get();
        return EloquentInventoryItemMapper::toEntities($eloquentInventoryItems);
    }

    public function findAllByTagAndSupplierId(string $tag, int $supplierId): Collection
    {
        // TODO: Implement findAllByTag() method.
    }
}
