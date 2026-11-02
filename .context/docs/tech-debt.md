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
| TD-01 | The layer folders are empty; Deptrac guards rules nothing exercises yet | B0 sets up the guard rails before any domain code, on purpose | B1 (#4) |
| TD-02 | The user model is Laravel's skeleton model, moved to `Infrastructure`; there is no domain account yet | Accounts are redesigned with authorisation | B5 (#8) |
| TD-03 | Feature tests run on SQLite; production runs MySQL | No repository exists yet, so no MySQL-specific query to test | B4 (#7): repository tests against MySQL 8.4 in CI |
| TD-04 | The v1 fixtures (store 1 mapping, routes, floor plan) are not in the default branch | They are needed only by the routing golden tests | B2 (#5): copied from the `v1` tag into `tests/Fixtures/` |
| TD-05 | No mutation testing | Coverage is the gate for now; mutation testing pays off once the domain exists | Infection on `app/Domain` after B3 (#6) |
| TD-06 | The Docker image is for development only (bind mount, Composer inside, no build stage) | Nothing is deployed | A production stage in `docker/app/Dockerfile` if the app is ever hosted |
| TD-07 | The welcome page is Laravel's | The interface is rebuilt in B6 | B6 (#9) |
| TD-08 | `minimatch` is forced to 11 through `overrides` in `package.json` | ESLint 10 and `eslint-plugin-sonarjs` ask for `^10`, and GHSA-g3hv-75wg-m249 is fixed only in 11.0.1 | Remove the override once both accept `minimatch` 11 |
