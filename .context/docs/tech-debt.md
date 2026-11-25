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
| TD-05 | No mutation testing | Coverage is the gate for now; mutation testing pays off once the domain exists | Infection on `app/Domain` after B3 (#6) |
| TD-06 | The Docker image is for development only (bind mount, Composer inside, no build stage) | Nothing is deployed | A production stage in `docker/app/Dockerfile` if the app is ever hosted |
| TD-08 | `minimatch` is forced to 11 through `overrides` in `package.json` | ESLint 10 and `eslint-plugin-sonarjs` ask for `^10`, and GHSA-g3hv-75wg-m249 is fixed only in 11.0.1 | Remove the override once both accept `minimatch` 11 |
| TD-10 | v1's mapping also detected the sector and partition rectangles on the plan (`setores`, `divisorias`); v2 does not | A store's layout is data in v2 (B4's seed, B7's form), and the checklist of B2 asked for the grid only | B7 (#10), if store creation should propose the rectangles from the plan |
| TD-12 | A visit always starts at the entrance; v1's `codigoSetorOrigem` (plan again from the sector the shopper is in) is not ported | v1's web screen never passed it, and indoor positioning is out of scope; `VisitPlanner` takes the start from the store | B6 (#9), if the visit screen lets the shopper re-plan from a sector |
| TD-14 | No screen grants or removes the administrator role: an account becomes one through the seed (`DemoAdministratorSeeder`) or the `role` column | B5's checklist is the rule (store management only for administrators), not the managing of accounts | B7 (#10) or the admin screens, if more than the demo administrator is needed |
| TD-15 | The pages are English only | The rewrite's language is English (development-conventions.md); a Portuguese translation is a release decision | B8 (#11), if v2 ships in Portuguese |
| TD-16 | New sector and partition ids are the largest plus one, read before writing; two administrators adding parts at the same instant could collide (the unique key refuses the second) | One administrator at a time is the expected use; database-assigned ids would need the aggregate to accept parts without ids | If several administrators manage stores at once |

## Closed

| Id | Gap | Closed by |
| --- | --- | --- |
| TD-01 | No route mapped a floor plan or called the routing use cases | B6 (#9) and B7 (#10): every use case is behind a page ([ADR 0008](/docs/adr/0008-store-management.md)) |
| TD-02 | The user model was Laravel's skeleton model, moved to `Infrastructure`, with no domain account | B5 (#8): `App\Domain\Account\Account`, mapped from the `users` row by `EloquentAccountRepository` ([ADR 0006](/docs/adr/0006-accounts-and-authorisation.md)) |
| TD-03 | Feature tests ran on SQLite; production runs MySQL | B4 (#7): the feature tests run on MySQL 8.4 (`bway_testing`), locally and as a service of the CI `test` job ([ADR 0005](/docs/adr/0005-persistence.md)) |
| TD-04 | The v1 fixtures (store 1 mapping, routes, floor plan) were not in the default branch | B2 (#5): copied verbatim from the `v1` tag into `tests/Fixtures/v1/store-1/`, used by `tests/Feature/Golden/StoreOneTest.php` |
| TD-07 | The welcome page was Laravel's | B6 (#9): a home page in the app's layout ([ADR 0007](/docs/adr/0007-web-interface.md)) |
| TD-09 | Grids and cached routes were JSON files on the `local` disk (`stores/<id>/`), not database rows | B4 (#7): moved to the `store_grids` and `routes` tables, the filesystem adapters removed ([ADR 0005](/docs/adr/0005-persistence.md)) |
| TD-11 | A store's routes were found during the first visit's request | B7 (#10): a queued job finds every pair after the layout or the plan changes |
| TD-13 | `MapFloorPlan` saved the grid and forgot the routes outside one transaction | B7 (#10): `StoreGridWriter` does both on the `Transaction` port |
