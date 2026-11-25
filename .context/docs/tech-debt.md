---
type: doc
name: tech-debt
description: Known gaps, with the reason each is still open and what closes it
category: debt
generated: 2026-11-01
status: filled
scaffoldVersion: "2.0.0"
---

# Technical debt

Known gaps, each a decision with a reason. When one is picked up, it becomes an issue and its number
goes here. A `TODO` in the code names its issue (`TODO(#12): ...`); this table holds the gaps that are
decisions rather than tasks.

| Id | Gap | Why it is open | What closes it |
| --- | --- | --- | --- |
| TD-01 | `app/Http` is empty: no route calls the use cases (`MapFloorPlan`, `FindRouteBetweenSectors`, `PlanVisit`); they run against MySQL in the feature tests, and `MapFloorPlan` in the seeder | The domain, its use cases and their storage came first, on purpose; HTTP is a later phase | The first controller (B5, #8, or B6, #9) |
| TD-02 | The user model is Laravel's skeleton model, moved to `Infrastructure`; there is no domain account yet | Accounts are redesigned with authorisation | B5 (#8) |
| TD-05 | No mutation testing | Coverage is the gate for now; mutation testing pays off once the domain exists | Infection on `app/Domain` after B3 (#6) |
| TD-06 | The Docker image is for development only (bind mount, Composer inside, no build stage) | Nothing is deployed | A production stage in `docker/app/Dockerfile` if the app is ever hosted |
| TD-07 | The welcome page is Laravel's | The interface is rebuilt in B6 | B6 (#9) |
| TD-08 | `minimatch` is forced to 11 through `overrides` in `package.json` | ESLint 10 and `eslint-plugin-sonarjs` ask for `^10`, and GHSA-g3hv-75wg-m249 is fixed only in 11.0.1 | Remove the override once both accept `minimatch` 11 |
| TD-10 | v1's mapping also detected the sector and partition rectangles on the plan (`setores`, `divisorias`); v2 does not | A store's layout is data in v2 (B4's seed, B7's form), and the checklist of B2 asked for the grid only | B7 (#10), if store creation should propose the rectangles from the plan |
| TD-11 | The first visit planned after a store is mapped finds its routes with A* during the request: up to n² searches for n stops: the 25 of a five-sector visit of store 1 take about 0.15 s in the golden test, and the count grows with the square of the stops | The route cache makes every later plan read them; finding every pair when the plan is mapped would make the mapping slower for pairs no list may need | B7 (#10): find a store's routes in a queued job after mapping, if the first plan is noticeably slow |
| TD-12 | A visit always starts at the entrance; v1's `codigoSetorOrigem` (plan again from the sector the shopper is in) is not ported | v1's web screen never passed it, and indoor positioning is out of scope; `VisitPlanner` takes the start from the store | B6 (#9), if the visit screen lets the shopper re-plan from a sector |
| TD-13 | `MapFloorPlan` saves the new grid and then forgets the store's routes in two statements, outside one transaction: if the second fails, routes found on the old grid are still served | The use case is in `Application`, which has no transaction port, and only the seeder maps a plan today | B7 (#10): map a plan in one transaction (a unit-of-work port, or one adapter that keeps both), when store creation maps plans from uploads |

## Closed

| Id | Gap | Closed by |
| --- | --- | --- |
| TD-03 | Feature tests ran on SQLite; production runs MySQL | B4 (#7): the feature tests run on MySQL 8.4 (`bway_testing`), locally and as a service of the CI `test` job ([ADR 0005](/docs/adr/0005-persistence.md)) |
| TD-04 | The v1 fixtures (store 1 mapping, routes, floor plan) were not in the default branch | B2 (#5): copied verbatim from the `v1` tag into `tests/Fixtures/v1/store-1/`, used by `tests/Feature/Golden/StoreOneTest.php` |
| TD-09 | Grids and cached routes were JSON files on the `local` disk (`stores/<id>/`), not database rows | B4 (#7): moved to the `store_grids` and `routes` tables, the filesystem adapters removed ([ADR 0005](/docs/adr/0005-persistence.md)) |
