<?php

declare(strict_types=1);

namespace App\Infrastructure\Storage;

use App\Domain\Grid\CellKind;
use App\Domain\Grid\Grid;
use App\Domain\Store\GridNotMapped;
use App\Domain\Store\StoreGridRepository;
use App\Domain\Store\StoreId;
use Illuminate\Contracts\Filesystem\Filesystem;
use UnexpectedValueException;

/**
 * Keeps each store's grid as `stores/<id>/grid.json` on a Laravel disk: one string per row, one
 * cell code (0 to 4, as in v1) per character.
 */
final readonly class FilesystemStoreGridRepository implements StoreGridRepository
{
    /**
     * Takes the disk the grids are kept on.
     */
    public function __construct(private Filesystem $disk) {}

    /**
     * Writes the grid, replacing the store's earlier one.
     */
    public function save(StoreId $storeId, Grid $grid): void
    {
        $rows = array_map(
            static fn (array $row): string => implode('', array_map(static fn (CellKind $kind): int => $kind->value, $row)),
            $grid->rows(),
        );

        $this->disk->put($this->path($storeId), json_encode(['rows' => $rows], JSON_THROW_ON_ERROR));
    }

    /**
     * Reads the store's grid.
     *
     * @throws GridNotMapped when the store has no grid file.
     * @throws UnexpectedValueException when the file is not a grid this class wrote.
     */
    public function forStore(StoreId $storeId): Grid
    {
        $path = $this->path($storeId);

        if (! $this->disk->exists($path)) {
            throw GridNotMapped::forStore($storeId);
        }

        return Grid::fromRows(array_map($this->cells(...), $this->rows((string) $this->disk->get($path))));
    }

    /**
     * The encoded rows of a grid file.
     *
     * @return list<string>
     *
     * @throws UnexpectedValueException when the file is not a JSON object with a list of digit strings.
     */
    private function rows(string $contents): array
    {
        $decoded = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
        $rows = is_array($decoded) ? ($decoded['rows'] ?? null) : null;

        if (
            ! is_array($rows)
            || ! array_is_list($rows)
            || array_filter($rows, $this->isNotRow(...)) !== []
        ) {
            throw new UnexpectedValueException('A grid file holds {"rows": [...]}, one string of cell codes per row.');
        }

        /**
         * Every entry was just checked to be a string of digits.
         *
         * @var list<string> $rows
         */
        return $rows;
    }

    /**
     * Tells whether a decoded value is anything but a non-empty string of digits.
     */
    private function isNotRow(mixed $row): bool
    {
        return ! is_string($row)
            || ! ctype_digit($row);
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

    /**
     * Where the store's grid is kept on the disk.
     */
    private function path(StoreId $storeId): string
    {
        return "stores/{$storeId->value}/grid.json";
    }
}
