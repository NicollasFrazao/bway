<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Grid\GridArea;
use App\Domain\Shared\Name;
use App\Domain\Store\PartitionId;
use App\Domain\Store\SectorId;
use App\Domain\Store\Store;
use App\Domain\Store\StoreId;
use App\Domain\Store\StoreNotFound;
use App\Domain\Store\StoreRepository;
use App\Infrastructure\Persistence\Eloquent\PartitionRecord;
use App\Infrastructure\Persistence\Eloquent\SectorRecord;
use App\Infrastructure\Persistence\Eloquent\StoreRecord;
use Illuminate\Database\ConnectionInterface;

/**
 * Stores kept in `stores`, with their layout in `sectors` and `partitions`; each is rebuilt as a
 * Store aggregate, which checks the layout again on the way out (the mapping is StoreRows').
 */
final readonly class EloquentStoreRepository implements StoreRepository
{
    /**
     * The relations loaded with every store.
     */
    private const array LAYOUT = ['sectors', 'partitions'];

    /**
     * Takes the connection whose transactions make a write all or nothing.
     */
    public function __construct(private ConnectionInterface $connection) {}

    /**
     * Loads one store with its sectors and partitions, each in the order of their ids.
     *
     * @throws StoreNotFound when no row has that id.
     */
    public function get(StoreId $id): Store
    {
        $record = StoreRecord::query()->with(self::LAYOUT)->find($id->value) ?? throw StoreNotFound::withId($id);

        return StoreRows::toStore($record);
    }

    /**
     * Every store, in the order of their ids.
     *
     * @return list<Store>
     */
    public function all(): array
    {
        return array_values(
            StoreRecord::query()->with(self::LAYOUT)->orderBy('id')->get()->map(StoreRows::toStore(...))->all(),
        );
    }

    /**
     * Writes the store row and its entrance in one transaction, then loads the aggregate.
     */
    public function add(Name $name, GridArea $entrance): Store
    {
        $id = $this->connection->transaction(static fn (): int => StoreRows::insert($name, $entrance));

        return $this->get(new StoreId($id));
    }

    /**
     * Rewrites the store's name and layout in one transaction.
     *
     * @throws StoreNotFound when no row has the store's id.
     */
    public function save(Store $store): void
    {
        $this->connection->transaction(static function () use ($store): void {
            $record = StoreRecord::query()->lockForUpdate()->find($store->id->value) ?? throw StoreNotFound::withId($store->id);
            StoreRows::rewrite($record, $store);
        });
    }

    /**
     * One more than the largest sector id.
     */
    public function nextSectorId(): SectorId
    {
        $largest = SectorRecord::query()->max('id');

        return new SectorId((is_numeric($largest) ? (int) $largest : 0) + 1);
    }

    /**
     * One more than the largest partition id.
     */
    public function nextPartitionId(): PartitionId
    {
        $largest = PartitionRecord::query()->max('id');

        return new PartitionId((is_numeric($largest) ? (int) $largest : 0) + 1);
    }
}
