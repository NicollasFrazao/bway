# 0008. Store management: a store created from its plan, a layout written through the aggregate, routes found on a queue

- Status: accepted
- Date: 2026-11-25
- Issue: [#10](https://github.com/NicollasFrazao/bway/issues/10)

## Context

v1 had no way to create a store. Store 1 existed only in the 2020 team's database, its sectors typed
in by hand, and its routes were computed by the Python service whenever a pair was first asked for.

B4 made `StoreRepository` read-only: the seeders wrote stores through `Database\Seeders\StoreWriter`.
`MapFloorPlan` saved a grid and forgot the store's routes in two statements (TD-13). The first visit
of a new store found its routes during the request (TD-11).

## Decision

- **A store is created from its name, its floor plan and its entrance** (`CreateStore`).
  - The plan is mapped first, so an unreadable image or a plan without a wall frame writes nothing.
  - The store with its entrance and its grid are then written in one transaction, so an entrance
    beyond the grid leaves no store behind.
  - The Store aggregate needs exactly one entrance, so a store cannot exist without one.
- **`StoreRepository` writes:**
  - `add(name, entrance)` gives the aggregate with its ids;
  - `save(store)` rewrites the layout, deleting the parts the store no longer has (their assortment
    and routes go with them through the foreign keys);
  - `nextSectorId()` and `nextPartitionId()` give fresh ids.
  - The Eloquent adapter keeps the row mapping in `StoreRows`.
- **Every layout change goes through the Store aggregate.**
  - `EditSectors` and `EditPartitions` build the new aggregate.
  - `LayoutWriter` checks it against the grid and saves it with the store's routes forgotten, in one
    transaction.
  - A layout the domain refuses (overlap, outside the grid, no entrance) is never written.
  - The domain's layout errors share a base, `LayoutRefused`, and the grid's area errors share
    `InvalidGridArea`, so a form turns any of them into one message.
- **Mapping is one transaction** (`StoreGridWriter`, on the `Transaction` port): the new grid and the
  forgetting of the old routes are kept together (TD-13 closed).
- **Routes are found on a queue.**
  - `TrackRouteGeneration::start` pushes `GenerateStoreRoutesJob`, unique per store, run by the new
    `queue` service of `docker-compose.yml`.
  - The job runs `GenerateStoreRoutes`, which searches every ordered pair not known yet.
  - **The route cache now keeps the pairs with no route too** (`unreachable_routes`), so:
    - the progress is read from the cache itself: found, unreachable, total;
    - the page lists the sectors no walkway reaches, which is the "every sector reachable from the
      entrance" check;
    - `FindRouteBetweenSectors` answers a known unreachable pair without searching again.
  - TD-11 closed: a store's routes can be found before anyone visits it.
- **The admin pages** (behind `can:manage-stores`):
  - the store list and a new-store form;
  - the layout page, which shows the mapped grid as a picture (`GridPainter`, GD, one pixel per cell
    in the legend's colours) with the sectors and partitions drawn over it in SVG;
  - forms to add, move and remove sectors and partitions;
  - upload of a new plan;
  - the assortment, one select per product;
  - the routes' progress with the unreachable pairs.
- **Assortment:** `Assortment::ofPlacements` builds the assortment from the form's placements, keyed
  by product, so a product can never be placed twice.

## Alternatives considered

- **Detecting the sectors from the plan's blue outlines**, as v1's mapping started to do (TD-10).
  Rectangles typed in cells are exact; detection would still need the same forms to name and fix
  them.
- **A status table for the route job.** The cache already knows which pairs are found and which have
  no route; a second table could disagree with it.
- **Finding the routes inside the request** that saves a layout. A large store has hundreds of pairs.
- **Keeping the uploaded image.** The grid is what the routes and the layout use; the picture of the
  grid shows what the mapping understood, which is what an administrator needs to check.

## Consequences

- Development needs the queue worker (`make up` starts it). The tests use the sync queue.
- A store's id, sector ids and partition ids are assigned before writing (`next*Id()` is the largest
  plus one), which is enough for one administrator at a time (TD-16).
- TD-01, TD-11 and TD-13 are closed. TD-10 (detecting the rectangles) and TD-14 (granting the role)
  stay open.
