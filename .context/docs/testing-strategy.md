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

Feature tests run on **MySQL 8.4**, the image of `docker-compose.yml`, because the queries and the
constraints that repeat the domain rules are MySQL's ([ADR 0005](/docs/adr/0005-persistence.md)).
`phpunit.xml` sets `DB_CONNECTION=mysql` and `DB_DATABASE=bway_testing`, a database of its own, so a
test run never wipes the app's data; the host and the credentials come from `.env` (the compose
service). MySQL creates `bway_testing` when its volume is new (`docker/mysql/initdb/`); a volume
from before B4 needs `docker compose down -v` once. In CI the `test` job runs the same image as a
service and points `DB_HOST` at it. Tests that touch the database use `RefreshDatabase`.

Cache, session and mail use array drivers. The other variables of `phpunit.xml` win over `.env`:
Laravel's environment loader never overwrites a variable that is already set. `phpunit.xml` also
sets a test-only `APP_KEY`, so the tests need no generated key; CI copies `.env.example` to `.env`
before running them, as a developer's checkout has one.

Unit tests never touch the database, so `tests/Unit/` runs without the stack.

## Conventions

- One behaviour per test, named as a sentence: `it('reports a surface edited by hand and a missing
  one')`.
- Arrange, act, assert, separated by blank lines when the test is longer than three lines.
- No `$this->property` state in Pest closures: PHPStan cannot type it. Use a typed helper instead
  (`tests/Support/ScratchRepository.php` is the model) and the namespaced Laravel functions
  (`Pest\Laravel\get`, the `command()` helper in `tests/Pest.php`).
- Files the test writes go under the system temp directory, through a helper that deletes them after
  each test (`ScratchRepository::deleteAll()` in `tests/Pest.php`).
- A port (repository interface) is tested by a **contract**: one test file per port in
  `tests/Feature/Contracts/`, its tests run `->with()` a dataset of adapters. The in-memory adapter in
  `tests/Support/Domain/` is the reference; the Eloquent adapter joins the dataset and runs on
  MySQL. What only one adapter does (its storage format, refusing a row it did not write, a save that
  is all or nothing) is tested next to it, in `tests/Feature/Infrastructure/Persistence/`. Hand-made domain objects come from
  small builders such as `tests/Support/StoreLayout.php`; a store is written to the database through
  `Database\Seeders\StoreWriter`, as the seed does.
- Grids and floor plans are drawn as text: `tests/Support/GridDrawing.php` (`#` wall, `o` walkway,
  `A` and `B` the start and the target, routes traced back with `*`), `DrawnFloorPlan.php` and
  `TextFloorPlanDecoder.php` (pixels in the legend's colours), `CornerShop.php` (a store small
  enough to draw), `AisleShop.php` (one aisle, with an assortment and lists, for the visit).
  `tests/Support/Domain/FixedRouteLengths.php` gives route lengths from a hand-made table and
  records the pairs asked for.
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
- the use cases map the plan, cache a route in MySQL (`store_grids`, `routes`) and forget it on a new
  mapping.

A difference not listed there fails. [ADR 0003](/docs/adr/0003-floor-plan-mapping-and-routing.md)
records why each listed one is accepted.

`tests/Feature/Golden/StoreOneVisitTest.php` plans a visit of store 1 (sector 2 as the entrance) for
a sample list that needs the five other sectors and has one product the store does not sell. It
asserts that every route the visit takes is as long as v1 stored it, that nearest neighbour gives
v1's order (3, 6, 5, 10, 11: 1,259 steps), and that the `PlanVisit` use case, with the production
binding of 2-opt over nearest neighbour, gives 3, 5, 6, 10, 11 (1,193 steps), the shortest of the
120 orders, with the items grouped by sector and the unsold product reported.
[ADR 0004](/docs/adr/0004-visit-planning.md) records why.

## What each phase must test

| Phase | Tests |
| --- | --- |
| B1 domain | Every invariant: no duplicate product in a list, acquiring removes an item, exactly one entrance per store; every domain exception |
| B2 routing | Golden tests against the mapping and routes of store 1 committed in `v1` (see above); unreachable target, start equals target, walls and partitions block movement; the contracts of `StoreGridRepository` and `RouteCache` against their in-memory and filesystem adapters |
| B3 visit | Deterministic order on small hand-made grids (`tests/Support/AisleShop.php`, where nearest neighbour is not the shortest) and hand-made distance tables; products not sold in the store are reported; the golden visit of store 1 (see above); the contract of `AssortmentRepository` |
| B4 persistence | Each repository against MySQL, by contract (`tests/Feature/Contracts/`); every constraint that repeats a domain rule refuses its row and the cascades delete a store's data (`tests/Feature/Database/SchemaTest.php`); the seed is idempotent and the demo user plans, shops and plans again the visit of store 1 (`SeedersTest.php`) |
| B5 accounts | Every account rule in unit tests on in-memory adapters (`InMemoryAccountRepository`, `PrefixHasher`); the contract of `AccountRepository`; sign-up, sign-in (the same answer for a wrong password and an unknown email, five failures a minute), sign-out and password reset end to end (`tests/Feature/Accounts/`); other users' ids sent every way v1 accepted them change only the session's list, v1's URLs answer 404, and a form without its token gets 419 (`tests/Feature/ShoppingList/OwnListTest.php`); store management forbidden to a shopper (`tests/Feature/Admin/`) |
| B6, B7 | Feature tests of the main flows and of the admin flow and its queued job |

## Today

524 tests (datasets counted one per case): the welcome page and the health route, the `context:*`
commands and their classes, the user model (password hashing, hidden attributes), the accounts and
the list and store pages of B5 (sign-up, sign-in, rate limits, password reset, a user's own list,
the `manage-stores` gate, CSRF), the domain in
`tests/Unit/Domain/` — every invariant of the shopping list and the store (layout overlap and fit,
the assortment included), the value objects, the legend, the mapper, the A* search on drawn grids,
the visit planner and both ordering strategies, every domain exception — the B2 and B3 use cases in
`tests/Unit/Application/`, the GD decoder, the contracts of the seven repository ports against their
in-memory and Eloquent adapters, the adapters' own cases, the schema constraints, the seeders, the
composition root, and the golden tests of store 1 (mapping, routes and visit). They cover 99.8% of the
lines of `app/`; the line left is the guard against a password broker that hands over a user without
an integer id.
