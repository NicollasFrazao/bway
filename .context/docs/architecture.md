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
| Eloquent model, repository implementation | `app/Infrastructure/Persistence/` | `EloquentShoppingListRepository` |
| Floor-plan image reader | `app/Infrastructure/` | a GD-based reader behind the mapper's interface |
| Controller, form request, resource | `app/Http/` | `ShoppingListController` |
| Binding of an interface to its implementation | `app/Providers/AppServiceProvider.php` | |

`<Context>` is a folder per part of the domain (`ShoppingList`, `Store`, `Routing`, `Visit`), created
when its first class arrives.

### Design rules inside the layers

- Constructor injection only; no service location (`app()`, facades) outside `Http`,
  `Infrastructure` and `Providers`.
- Composition over inheritance. Classes are `final` unless they are designed for extension; value
  objects are `final readonly`.
- Variation points are interfaces in `Domain` with one implementation each until a second one is
  needed (for example `Heuristic`, `VisitOrderingStrategy`).
- Domain errors are dedicated exception classes, named for what went wrong, created by named
  constructors (`SourceMissing::at($path)` is the pattern, in `app/Console/Context/`).

## What exists today

- `app/Infrastructure/Persistence/Eloquent/User.php` — the skeleton's user model, moved out of
  `app/Models` so Eloquent stays in Infrastructure. Accounts are redesigned in B5 (#8).
- `app/Console/` — `ContextSurfaces` and the two `context:*` commands (see
  [tooling.md](tooling.md)).
- `app/Providers/AppServiceProvider.php` — empty composition root.
- `routes/web.php` — the welcome page; `/up` is Laravel's health route, used by the container's
  health check.

## Runtime

- `docker compose up`: the `app` service (FrankenPHP: Caddy and PHP 8.4 in one process, serving
  `public/` on port 8000) and `mysql` (MySQL 8.4). The app's entrypoint installs Composer
  dependencies on a fresh clone, creates `.env`, generates the key and runs the migrations.
- Sessions, cache and queue use the database drivers (Laravel's defaults); tests use SQLite in
  memory and array drivers (`phpunit.xml`).
