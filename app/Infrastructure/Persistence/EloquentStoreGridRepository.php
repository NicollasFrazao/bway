<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Grid\CellKind;
use App\Domain\Grid\Grid;
use App\Domain\Store\GridNotMapped;
use App\Domain\Store\StoreGridRepository;
use App\Domain\Store\StoreId;
use App\Infrastructure\Persistence\Eloquent\StoreGridRecord;
use UnexpectedValueException;

/**
 * The grid of each store, kept in `store_grids`: its width, its height and every cell code (0 to 4,
 * as in v1), row after row, in one string.
 */
final readonly class EloquentStoreGridRepository implements StoreGridRepository
{
    /**
     * Writes the grid, replacing the store's earlier one.
     */
    public function save(StoreId $storeId, Grid $grid): void
    {
        $cells = implode('', array_map(
            static fn (array $row): string => implode('', array_map(static fn (CellKind $kind): int => $kind->value, $row)),
            $grid->rows(),
        ));

        StoreGridRecord::query()->updateOrCreate(
            ['store_id' => $storeId->value],
            ['width' => $grid->width, 'height' => $grid->height, 'cells' => $cells],
        );
    }

    /**
     * Reads the store's grid.
     *
     * @throws GridNotMapped when the store has no grid row.
     * @throws UnexpectedValueException when the row is not a grid this class wrote.
     */
    public function forStore(StoreId $storeId): Grid
    {
        $record = StoreGridRecord::query()->where('store_id', $storeId->value)->first()
            ?? throw GridNotMapped::forStore($storeId);

        return Grid::fromRows(array_map($this->cells(...), $this->rows($record)));
    }

    /**
     * The encoded rows of a grid row, `width` codes each.
     *
     * @return list<string>
     *
     * @throws UnexpectedValueException when the codes are not width x height digits from 0 to 4.
     */
    private function rows(StoreGridRecord $record): array
    {
        if (
            $record->width < 1
            || strlen($record->cells) !== $record->width * $record->height
            || preg_match('/^[0-4]+$/', $record->cells) !== 1
        ) {
            throw new UnexpectedValueException(sprintf(
                'The grid of store %d does not hold %d x %d cell codes from 0 to 4.',
                $record->store_id,
                $record->width,
                $record->height,
            ));
        }

        return str_split($record->cells, $record->width);
    }

    /**
     * Decodes one row.
     *
     * @return list<CellKind>
     */
    private function cells(string $row): array
    {
        return array_map(static fn (string $code): CellKind => CellKind::from((int) $code), str_split($row));
    }
}
