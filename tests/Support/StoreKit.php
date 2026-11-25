<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Application\FloorPlan\StoreGridWriter;
use App\Application\Routing\FindRouteBetweenSectors;
use App\Application\Routing\TrackRouteGeneration;
use App\Application\Store\AreaInput;
use App\Application\Store\CreateStore;
use App\Application\Store\EditPartitions;
use App\Application\Store\EditSectors;
use App\Application\Store\LayoutWriter;
use App\Domain\FloorPlan\FloorPlanMapper;
use App\Domain\FloorPlan\LegendColorClassifier;
use App\Domain\Routing\AStarPathfinder;
use App\Domain\Routing\ManhattanDistance;
use App\Domain\Store\StoreId;
use Tests\Support\Domain\ImmediateTransaction;
use Tests\Support\Domain\InMemoryRouteCache;
use Tests\Support\Domain\InMemoryStoreGridRepository;
use Tests\Support\Domain\InMemoryStoreRepository;
use Tests\Support\Domain\RecordingRouteGenerationQueue;

/**
 * The in-memory adapters of store management and the use cases over them, with a U-shaped shop drawn
 * as text: walkways round the edges, floor in the middle (cells 2..4 of rows 2..4).
 */
final readonly class StoreKit
{
    /**
     * The stores.
     */
    public InMemoryStoreRepository $stores;

    /**
     * Their grids.
     */
    public InMemoryStoreGridRepository $grids;

    /**
     * Their routes and pairs without one.
     */
    public InMemoryRouteCache $routes;

    /**
     * The queue of route generations.
     */
    public RecordingRouteGenerationQueue $queue;

    /**
     * Starts with no store.
     */
    public function __construct()
    {
        $this->stores = new InMemoryStoreRepository;
        $this->grids = new InMemoryStoreGridRepository;
        $this->routes = new InMemoryRouteCache;
        $this->queue = new RecordingRouteGenerationQueue;
    }

    /**
     * The U shop's floor plan, as text for TextFloorPlanDecoder.
     */
    public static function plan(): string
    {
        return implode("\n", ['RRRRRRR', 'ROOOOOR', 'RO...OR', 'RO...OR', 'RO...OR', 'ROOOOOR', 'RRRRRRR']);
    }

    /**
     * Creates the U shop with the entrance given.
     */
    public function create(string $name = 'U shop', ?AreaInput $entrance = null): StoreId
    {
        return new CreateStore(
            $this->stores,
            new FloorPlanMapper(new LegendColorClassifier, new TextFloorPlanDecoder),
            new StoreGridWriter($this->grids, $this->routes, new ImmediateTransaction),
            new ImmediateTransaction,
        )->handle($name, $entrance ?? new AreaInput(1, 1, 1, 1), self::plan());
    }

    /**
     * The sectors use case.
     */
    public function sectors(): EditSectors
    {
        return new EditSectors($this->stores, $this->writer());
    }

    /**
     * The partitions use case.
     */
    public function partitions(): EditPartitions
    {
        return new EditPartitions($this->stores, $this->writer());
    }

    /**
     * The writer of layouts.
     */
    private function writer(): LayoutWriter
    {
        return new LayoutWriter($this->stores, $this->grids, $this->routes, new ImmediateTransaction);
    }

    /**
     * The route search, with A*.
     */
    public function find(): FindRouteBetweenSectors
    {
        return new FindRouteBetweenSectors($this->stores, $this->grids, $this->routes, new AStarPathfinder(new ManhattanDistance));
    }

    /**
     * The tracking of route generation, pushing to the recording queue.
     */
    public function tracking(): TrackRouteGeneration
    {
        return new TrackRouteGeneration($this->stores, $this->routes, $this->queue);
    }
}
