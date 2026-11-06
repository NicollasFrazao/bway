---
type: doc
name: testing-strategy
description: Unit and feature tests with Pest, the coverage gate, and what each phase must test
category: testing
generated: 2026-11-01
status: filled
scaffoldVersion: "2.0.0"
---

# Testing strategy

Tests are written with [Pest](https://pestphp.com) 4 on PHPUnit 12. `composer test:coverage` (the
`test` CI job) runs them all with pcov and fails under **80% line coverage of `app/`**.

## Layers

| Suite | Folder | Boots Laravel | For |
| --- | --- | --- | --- |
| Unit | `tests/Unit/` | **no** | Domain and application classes, and any class that takes its collaborators by constructor |
| Feature | `tests/Feature/` | yes (`Tests\TestCase`) | Routes, Artisan commands, repositories, the wiring of the provider |

`tests/Pest.php` binds `Tests\TestCase` to `Feature` only. Keep it that way: a domain test that
needs the container is a sign the class reaches outside the domain.

## Database

Feature tests run on SQLite in memory with array cache, session and mail drivers (`phpunit.xml`).
The `.env` of the Docker stack does not leak in: `phpunit.xml` sets the variables first, and
Laravel's environment loader never overwrites a variable that is already set.
`phpunit.xml` also sets a test-only `APP_KEY`, so the tests need no generated key; CI copies
`.env.example` to `.env` before running them, as a developer's checkout has one.

From B4 (#7) on, the repository tests run against MySQL 8.4 (the same image as `docker-compose.yml`,
as a service in the CI job), because the queries and constraints are MySQL's.

## Conventions

- One behaviour per test, named as a sentence: `it('reports a surface edited by hand and a missing
  one')`.
- Arrange, act, assert, separated by blank lines when the test is longer than three lines.
- No `$this->property` state in Pest closures: PHPStan cannot type it. Use a typed helper instead
  (`tests/Support/ScratchRepository.php` is the model) and the namespaced Laravel functions
  (`Pest\Laravel\get`, the `command()` helper in `tests/Pest.php`).
- Files the test writes go under the system temp directory, through a helper that deletes them after
  each test (`ScratchRepository::deleteAll()` in `tests/Pest.php`).
- A port (repository interface) is tested by a **contract**: one test file per port, its tests run
  `->with()` a dataset of adapters. The in-memory adapter in `tests/Support/Domain/` is the
  reference; every production adapter joins the dataset (B4 moves the contract to a suite that can
  reach MySQL). Hand-made domain objects come from small builders such as
  `tests/Support/StoreLayout.php`.
- Grids and floor plans are drawn as text: `tests/Support/GridDrawing.php` (`#` wall, `o` walkway,
  `A` and `B` the start and the target, routes traced back with `*`), `DrawnFloorPlan.php` and
  `TextFloorPlanDecoder.php` (pixels in the legend's colours), `CornerShop.php` (a store small
  enough to draw).
- Literal numbers and strings are fine in tests: they are the readable form of a case. Tests are
  exempt from the magic-number check, not from PHPStan or PHP_CodeSniffer.

## Golden tests (v1 fixtures)

`tests/Fixtures/v1/store-1/` holds, verbatim from the `v1` tag, the floor plan of store 1
(`plantas/estabelecimento.jpg`), the grid v1 mapped from it (`mapeamentos/1.json`) and the 36 routes
v1 stored (`rotas/1-<from>-<to>.json`). `tests/Support/V1StoreOne.php` reads them, undoing v1's route
format (target first, target twice, start missing). `tests/Feature/Golden/StoreOneTest.php` maps the
JPEG with GD and asserts:

- the grid equals v1's on every cell outside row and column 477, where v1 slipped (both checked
  separately);
- each of v1's routes starts and ends at the centres of its sectors, which proves the sector ids;
- 25 routes equal v1's cell for cell; the other five are named in `V1_ROUTE_DIFFERENCES`, each with
  its own assertion (three shorter than v1's, one as short along other cells, one stored by v1 from
  an earlier plan);
- the use cases map the plan, cache a route on disk and forget it on a new mapping.

A difference not listed there fails. [ADR 0003](/docs/adr/0003-floor-plan-mapping-and-routing.md)
records why each listed one is accepted.

## What each phase must test

| Phase | Tests |
| --- | --- |
| B1 domain | Every invariant: no duplicate product in a list, acquiring removes an item, exactly one entrance per store; every domain exception |
| B2 routing | Golden tests against the mapping and routes of store 1 committed in `v1` (see above); unreachable target, start equals target, walls and partitions block movement; the contracts of `StoreGridRepository` and `RouteCache` against their in-memory and filesystem adapters |
| B3 visit | Deterministic order on small hand-made grids; products not sold in the store are reported |
| B4 persistence | Each repository against MySQL; the seeded store supports the whole flow |
| B5 accounts | Access to another user's list is denied; rate limiting; CSRF |
| B6, B7 | Feature tests of the main flows and of the admin flow and its queued job |

## Today

330 tests (datasets counted one per case): the welcome page and the health route, the `context:*`
commands and their classes, the user model (password hashing, hidden attributes, the seeder), the
domain in `tests/Unit/Domain/` — every invariant of the shopping list and the store (layout overlap
and fit included), the value objects, the legend, the mapper, the A* search on drawn grids, every
domain exception, and the contracts of the B1 ports against their in-memory adapters — the B2 use
cases in `tests/Unit/Application/`, the GD decoder, the filesystem adapters (by contract), the
composition root, and the golden tests of store 1. They cover 100% of the lines of `app/`.
