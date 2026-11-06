---
type: doc
name: architecture
description: Layers and their dependency rules, where each kind of class goes, and the composition root
category: architecture
generated: 2026-11-01
status: filled
scaffoldVersion: "2.0.0"
---

# Architecture

One Laravel 13 application on PHP 8.4 and MySQL 8.4. Laravel lives at the edges: HTTP, persistence,
queues, configuration. The rules of the problem (lists, stores, grids, routes, visit order) are plain
PHP that can be read and tested without the framework. The decision and its trade-offs are in
[ADR 0001](/docs/adr/0001-layered-architecture.md).

## Layers

```
app/
  Domain/          entities, value objects, domain services,         depends on nothing outside Domain
                   repository interfaces, domain exceptions          (PHP's own classes allowed)
  Application/     use cases (command handlers), DTOs                Domain
  Infrastructure/  Eloquent models and repositories, file storage,   Domain, Application, Laravel,
                   image reading, queued jobs                        database/ factories
  Http/            controllers, form requests, resources, views      Domain, Application, Laravel
  Console/         developer tooling (context:export, context:check) Laravel
  Providers/       composition root: binds ports to adapters         everything
```

### Layer rules

Checked by `composer check:architecture` (Deptrac, `deptrac.yaml`, run with `--fail-on-uncovered`):

| Rule | Why |
| --- | --- |
| `Domain` depends on nothing outside `Domain` | The A* search, the visit order and the list rules are tested in milliseconds, with hand-made grids, and cannot reach I/O or the framework by accident. |
| `Application` depends only on `Domain` | A use case talks to storage through the repository interfaces declared in `Domain`; swapping Eloquent for anything else touches `Infrastructure` and the provider only. |
| `Infrastructure` and `Http` never depend on each other | A controller reaches the database only through a use case. |
| Only `Providers` sees every layer | Bindings live in one place, so the wiring is readable in one file. |
| Every third-party class belongs to the `Vendor` layer | A dependency on a namespace Deptrac does not know is reported as uncovered and fails the job: a new package has to be classified before it is used. |

### Where things go

| Kind of class | Folder | Example (planned) |
| --- | --- | --- |
| Entity, aggregate root | `app/Domain/<Context>/` | `ShoppingList`, `Store`, `Sector` |
| Value object (immutable, `final readonly`) | `app/Domain/<Context>/` | `GridCoordinate`, `Route`, `ProductName` |
| Domain service | `app/Domain/<Context>/` | `AStarPathfinder`, `VisitPlanner` |
| Repository interface (port) | `app/Domain/<Context>/` | `ShoppingListRepository`, `StoreRepository` |
| Domain exception | `app/Domain/<Context>/` | `DuplicateListItem`, `StoreWithoutEntrance` |
| Use case and its input/output DTOs | `app/Application/<Context>/` | `AddProductToList`, `PlanVisit` |
| Application-side adapter of a domain port | `app/Application/<Context>/` | `RouteLengthsBetweenSectors`, behind `RouteLengths` |
| Eloquent model, repository implementation | `app/Infrastructure/Persistence/` | `EloquentShoppingListRepository` |
| Floor-plan image decoder | `app/Infrastructure/FloorPlan/` | `GdFloorPlanDecoder`, behind the `FloorPlanDecoder` port |
| File-backed adapter of a port | `app/Infrastructure/Storage/` | `FilesystemRouteCache`, on a Laravel disk |
| Controller, form request, resource | `app/Http/` | `ShoppingListController` |
| Binding of an interface to its implementation | `app/Providers/` (`AppServiceProvider`, `VisitServiceProvider`) | |

`<Context>` is a folder per part of the domain, created when its first class arrives: `Shared`
(identifiers, `Name`, the `DomainError` base), `Catalog`, `ShoppingList`, `Store`, `Grid`,
`FloorPlan`, `Routing` and `Visit`. A context may use `Shared` and the contexts it builds on
(`ShoppingList` uses `Catalog`; `Store` uses `Grid` and, for its assortment, `Catalog`; `FloorPlan`
uses `Grid`; `Routing` uses `Grid` and `Store`; `Visit` uses `ShoppingList`, `Store` and `Routing`);
`Grid` uses only `Shared`. The choices behind the model are in
[ADR 0002](/docs/adr/0002-domain-model.md), for mapping and routing in
[ADR 0003](/docs/adr/0003-floor-plan-mapping-and-routing.md), and for the visit in
[ADR 0004](/docs/adr/0004-visit-planning.md).

### Design rules inside the layers

- Constructor injection only; no service location (`app()`, facades) outside `Http`,
  `Infrastructure` and `Providers`.
- Composition over inheritance. Classes are `final` unless they are designed for extension; value
  objects are `final readonly`.
- Variation points are interfaces in `Domain` with one implementation each until a second one is
  needed (for example `Heuristic`, `VisitOrderingStrategy`).
- Domain errors are dedicated exception classes extending `App\Domain\Shared\DomainError`, named
  for what went wrong, created by named constructors (`DuplicateListItem::forProduct($id)`).

## What exists today

- `app/Domain/` — the domain model of B1 (#4): `Catalog` (`Product`, `ProductName`, the
  `ProductCatalog` port), `ShoppingList` (the `ShoppingList` aggregate, `ListItem`, the
  `ShoppingListRepository` port), `Store` (the `Store` aggregate, `Sector`, `SectorKind`, `Partition`,
  the `StoreRepository` port), `Grid` (`GridCoordinate`, `GridArea`) and `Shared`. B2 (#5) added
  `Grid` (the `Grid` value object and `CellKind`), `FloorPlan` (`FloorPlanMapper`, the
  `ColorClassifier` strategy and its `LegendColorClassifier`, the `FloorPlanDecoder` port),
  `Routing` (`Route`, the `Pathfinder` interface and `AStarPathfinder` with its `Heuristic`, the
  `RouteCache` port) and, in `Store`, the `StoreGridRepository` port and the layout checks. The B1
  ports have no production adapter yet: the in-memory adapters in `tests/Support/Domain/` serve the
  contract tests until B4 (#7). B3 (#6) added, in `Store`, the `Assortment` of a store (one
  `SectorProduct` per product, `ProductInSeveralSectors`) and its `AssortmentRepository` port, and
  the `Visit` context: `VisitPlanner`, the `VisitOrderingStrategy` interface with
  `NearestNeighbourOrdering` and `TwoOptOrdering`, the `RouteLengths` port, `DistanceTable`,
  `VisitPlan` and `VisitStop`.
- `app/Application/` — the use cases of B2: `FloorPlan\MapFloorPlan` (map a store's plan, keep the
  grid, forget the store's routes) and `Routing\FindRouteBetweenSectors` (centre to centre, cached
  per ordered pair); and of B3: `Visit\PlanVisit` (the visit of a store for a user's list) and
  `Routing\RouteLengthsBetweenSectors`, the adapter of the `RouteLengths` port that measures through
  `FindRouteBetweenSectors`. No controller calls them yet (B6, #9; B7, #10).
- `app/Infrastructure/FloorPlan/` — `GdFloorPlanDecoder` and `GdFloorPlanImage` (GD).
- `app/Infrastructure/Storage/` — `FilesystemStoreGridRepository` and `FilesystemRouteCache`, which
  keep `stores/<id>/grid.json` and `stores/<id>/routes/<from>-<to>.json` on the `local` disk.
- `app/Infrastructure/Persistence/Eloquent/User.php` — the skeleton's user model, moved out of
  `app/Models` so Eloquent stays in Infrastructure. Accounts are redesigned in B5 (#8).
- `app/Console/` — `ContextSurfaces` and the two `context:*` commands (see
  [tooling.md](tooling.md)).
- `app/Providers/` — the composition root. `AppServiceProvider` binds the B2 ports
  (`ColorClassifier`, `FloorPlanDecoder`, `Heuristic`, `Pathfinder`, `StoreGridRepository`,
  `RouteCache`) to their adapters; `VisitServiceProvider` binds `RouteLengths` and the
  `VisitOrderingStrategy` (2-opt over nearest neighbour). Two providers keep each within PHPMD's
  coupling limit.
- `routes/web.php` — the welcome page; `/up` is Laravel's health route, used by the container's
  health check.

## Runtime

- `docker compose up`: the `app` service (FrankenPHP: Caddy and PHP 8.4 in one process, serving
  `public/` on port 8000) and `mysql` (MySQL 8.4). The app's entrypoint installs Composer
  dependencies on a fresh clone, creates `.env`, generates the key and runs the migrations.
- Sessions, cache and queue use the database drivers (Laravel's defaults); tests use SQLite in
  memory and array drivers (`phpunit.xml`).
