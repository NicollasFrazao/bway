# 0003. Floor-plan mapping and routing: v1's legend, a true A* on walkways, routes cached per pair

- Status: accepted
- Date: 2026-11-05
- Issue: [#5](https://github.com/NicollasFrazao/bway/issues/5)

## Context

v1 mapped a store's floor plan and searched routes in a Python/Flask service (`public/dist/py/app.py`
at the `v1` tag). `mapearPlanta` read the plan with Pillow and wrote a matrix of cell codes;
`gerarRota` searched between the centres of two sectors and stored each route as
`storage/app/public/estabelecimentos/rotas/<store>-<from>-<to>.json`, which `CarrinhoController`
counted to order a visit. B2 ports both into PHP, and the golden tests compare the port with the
mapping and the 36 routes of store 1 that v1 committed (`tests/Fixtures/v1/store-1/`).

Replaying v1 on its own data showed what the port has to keep and what it should not:

- **The legend.** Red is wall, blue a sector outline, green a partition, orange (`#FF6600`) a
  walkway, everything else free floor, with tolerances (a channel ≥ 250 is on, ≤ 10 is off; orange's
  green is 90 to 110) that absorb JPEG noise. GD and Pillow decode the plan of store 1 to identical
  pixels.
- **Routes run only on walkways.** v1 queued a neighbour only if its code was 4 (walkway); free floor
  never was. The plan paints the aisles in orange, three cells wide, through the centre of every
  sector.
- **The frame had two slips.** v1 marked the frame by position, testing `y == width - 1` where it
  meant `x == width - 1`: the right-hand wall stayed floor and row 477 of store 1 (its width minus
  one) became a wall across the store. The router then turned every wall cell not on the border back
  into floor, which hid the second slip.
- **The search was not A*.** v1 ranked a cell by the Manhattan distance from the start plus the
  Manhattan distance to the target, instead of the steps actually walked plus the distance to the
  target, and never revised a cell's parent. On store 1, 3 of the 30 routes between different
  sectors are 26 steps longer than the shortest (10, 11 and 6 to sector 2: 554, 835 and 262 steps,
  where v1 itself found 528, 809 and 236 the other way).
- **One stored route is stale.** The route from sector 2 to sector 5 crosses 152 cells that v1's own
  committed grid marks as floor or sector outline; it was stored from an earlier plan.
- **A route's length was its JSON entries.** The file lists the target twice and leaves out the
  start, so `count()` gave steps + 1, and a missing file gave 0.
- **The mapping file did not reach the router.** `mapearPlanta` wrote every store to
  `mapeamento.json`; `gerarRota` read `<store>.json`.

## Decision

- **The mapping keeps v1's legend and frame rule and drops the slips.** `FloorPlanMapper` classifies
  every pixel through a `ColorClassifier` strategy (`LegendColorClassifier` holds v1's thresholds),
  walls included, so the frame is wall on all four sides. The frame is v1's: from the first wall
  pixel, as wide as the wall runs right and as tall as it runs down at the end of that run. A
  landscape plan is read turned a quarter turn counter-clockwise (`QuarterTurnedImage`), as v1's
  `rotate(90)` did. On store 1 the grid equals v1's on every cell outside row and column 477.
- **The image is read behind a port.** `FloorPlanDecoder` (Domain) turns file bytes into a
  `FloorPlanImage`; `GdFloorPlanDecoder` (Infrastructure) implements it with GD, which the image
  already has, refusing anything but JPEG, PNG, GIF and WebP and treating a GD warning (a PNG cut short) as
  unreadable; GD fills a truncated JPEG with grey without a warning, which maps to floor. Imagick would add a system library for no gain.
- **The grid is a value object with five kinds of cell** (`CellKind`: floor, wall, sector,
  partition, walkway, backed by v1's codes 0 to 4). Each row is kept as a string of codes: a store is
  about 235,000 cells.
- **The search is a true A*.** `AStarPathfinder` implements `Pathfinder`, with the estimate injected
  as a `Heuristic` (`ManhattanDistance`, admissible on a four-neighbour grid), so every route is a
  shortest one. A route enters only walkway cells; the start may be any cell, as in v1. Ties are
  broken deterministically: lowest estimated total, then lowest estimate left, then first queued,
  with neighbours queued right, left, down, up (v1's order). With this order 25 of v1's 30 routes come
  out cell for cell; the other five are listed in the golden test with their cause (three shorter
  than v1's, one of the same length along other cells, the stale one).
- **A route is a value object of cells, start and target included**, each one step from the last
  (`BrokenRoute` otherwise). Its length is its steps: 0 from a sector to itself, never a missing
  route. No route is `RouteNotFound`, an error, not a length.
- **Routes between sectors run centre to centre** (`GridArea::center()`: origin plus half the side,
  rounded down, as v1's `int(x + width / 2)`), through the use case `FindRouteBetweenSectors`.
- **Routes are cached per ordered pair** behind the `RouteCache` port and found once. The production
  adapter keeps them as `stores/<id>/routes/<from>-<to>.json` on the local disk, next to the grid
  (`stores/<id>/grid.json`, `StoreGridRepository`). Both are keyed by the store id, which removes
  v1's file mismatch. `MapFloorPlan` saves the new grid and forgets the store's routes, which were
  found on the old one. A failed search is not cached.
- **A store's layout is checked against its grid.** `Store` refuses at construction two sectors or
  partitions that share a cell (`OverlappingLayout`), and `MapFloorPlan` refuses a plan whose grid
  does not hold every sector and partition (`LayoutOutsideGrid`). These were left open by
  [ADR 0002](0002-domain-model.md).

## Alternatives considered

- **Reproducing v1's search exactly.** It would match 29 of the 30 routes (the stale one cannot be
  matched by any search on the committed grid), at the cost of returning routes 26 steps longer than
  the shortest and of calling A* something that is not. The issue asked for v1's routes "exactly";
  the golden test pins every route that v1 got right and documents, per pair, each it did not.
- **Free floor as walkable.** The routes would cut across the store wherever the plan leaves white
  space, and none of v1's routes would come out; the walkways are how the plan says where shoppers
  walk.
- **Keeping v1's frame slips** so the grid matched byte for byte: a wall across the middle of the
  store that the router has to undo is a bug, not behaviour.
- **The route cache in Laravel's cache store.** The database cache driver has no tags, so forgetting
  one store's routes on a new mapping would need a version key in every entry; files per store make
  it one directory delete. B4 may move both grid and routes to MySQL.

## Consequences

- No Python is needed to run or test the project; the golden tests run in CI on the committed
  fixtures (1.6 MB, marked `-diff` in `.gitattributes`).
- The visit planner (B3) orders sectors by route length in steps; v1's counts were one higher for
  every pair, which does not change a nearest-neighbour order.
- A floor plan must paint a walkway through the centre of every sector, or the routes to that sector
  fail with `RouteNotFound`; B7's store creation has to say so.
- The sector and partition rectangles v1's mapping also detected (`setores`, `divisorias`) are not
  ported: in v2 a store's layout comes from its data, not from the picture. The golden test reads
  them from the fixture to build store 1.
