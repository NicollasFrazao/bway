# 0004. Visit planning: one sector per product, lengths measured up front, nearest neighbour improved by 2-opt

- Status: accepted
- Date: 2026-11-05
- Issue: [#6](https://github.com/NicollasFrazao/bway/issues/6)

## Context

In v1 the visit was built by `CarrinhoController::show` at the `v1` tag. Read for its behaviour, it
did this:

- **Which sectors.** Every sector of the store that sells a product of the user's list, through the
  many-to-many table `setor_produto`. The sectors were sorted by name, and each product was attached
  to the cart (`carrinho_produto`) with the first of them that sells it; a product already in the
  cart kept the sector it had. A product no sector sells never reached the cart, and nothing said
  so.
- **From where.** The entrance (`ic_entrada`), or a sector passed as `codigoSetorOrigem`; the web
  screen never passed one.
- **In which order.** Greedy: from the start, measure every remaining sector, take the one with the
  smallest measure, make it the new start, repeat. The measure was
  `count(RotaController::show(...))`, the number of entries in the stored route file: steps + 1 for a
  stored route ([ADR 0003](0003-floor-plan-mapping-and-routing.md)), and **0 for a pair whose file
  did not exist**, so a sector without a stored route was always visited next. A straight-line
  measure was left commented out.

Replayed on store 1 (its six sectors and v1's stored routes, sector 2 taken as the entrance as
`tests/Support/V1StoreOne.php` does), a list that needs the five other sectors is ordered 3, 6, 5,
10, 11: 1,259 steps. The shortest of the 120 orders is 3, 5, 6, 10, 11: 1,193 steps, 66 fewer.
Going from 3 to its nearest sector, 6 (136 steps), then to 5 leaves a walk of 515 steps from 5 to
10; visiting 5 before 6 costs 157 more steps early and saves 223 at the end (6 to 10 is 292).

## Decision

- **What a store sells is an `Assortment`**, apart from the `Store` aggregate (it changes with the
  stock, not with the layout): a list of `SectorProduct` facts, at most **one sector per product
  per store** (`ProductInSeveralSectors` otherwise), loaded through the `AssortmentRepository` port.
  v1's table allowed several, but its visit used only the first by sector name; v2 makes the one
  sector explicit instead of depending on names.
- **The planner is a domain service behind a strategy.** `VisitPlanner::plan(Store, Assortment,
  ShoppingList)` groups the items by the sector that sells them, keeps the items no sector sells as
  `VisitPlan::$unsold`, and orders the sectors from the entrance with the injected
  `VisitOrderingStrategy`. A sector's items keep list order (v1 sorted them by name; display order
  is the reader's concern, as [ADR 0002](0002-domain-model.md) decided). The entrance is a stop like
  any other when it sells something, at 0 steps.
- **Lengths are real route lengths, measured before ordering.** The `RouteLengths` port gives the
  steps of the route between two sectors; its adapter, `RouteLengthsBetweenSectors` (Application),
  goes through `FindRouteBetweenSectors`, so a route is found with A* once and then served from the
  route cache. A pair without a route is `RouteNotFound` and the plan fails: it is never a length,
  short or otherwise. The planner measures, into a `DistanceTable`, the routes from the start and
  from every stop to every other stop (never back to the start, unless the start is a stop), so
  every strategy is a pure computation on a table and is tested with hand-made tables.
- **Two strategies.** `NearestNeighbourOrdering` is v1's greedy rule on the corrected lengths; a tie
  goes to the lower sector id. `TwoOptOrdering` takes the order another strategy proposes and, while
  reversing a run of consecutive stops makes the whole walk shorter, reverses it. It measures the
  whole walk for each candidate, so it does not assume a route is as long both ways (the start may be
  off the walkways).
- **The production binding is 2-opt over nearest neighbour** (`VisitServiceProvider`). It is never
  longer than nearest neighbour alone, deterministic, and on store 1 finds the shortest order.
  Nearest neighbour stays available as the strategy that reproduces v1.
- **A plan is computed, not stored.** v1 kept the cart and its sectors in `tb_carrinho` and
  `carrinho_produto`. In v2, acquiring an item is `ShoppingList::acquire()` (B1), and the next plan
  no longer has it. The `PlanVisit` use case loads the store, its assortment and the user's list
  and returns the plan.

## Alternatives considered

- **Several sectors per product**, choosing one while planning. Picking well is a harder problem
  (each product is a choice of stops, not a stop); picking by name or id, as v1 did, is arbitrary.
  If stores need it, the `Assortment` grows a rule and the planner a choice, both behind the same
  interfaces.
- **Nearest neighbour as the production strategy**, for v1 fidelity. It is what the issue named as
  the first implementation, and it is kept and tested; but it costs 66 extra steps on the one store
  v1 left, and the extension point exists so the better order can be used.
- **An exact order** (Held-Karp, O(n² · 2ⁿ)). Exact for the handful of sectors of a visit, but the
  cost doubles with every sector; 2-opt is O(n³) per pass and found the optimum on store 1. Another
  strategy behind the same interface if it is ever needed.
- **Asking for lengths lazily**, only for the pairs the strategy looks at. Nearest neighbour needs
  about half of the pairs; 2-opt needs all of them. Measuring up front keeps strategies free of I/O,
  and each route is found once per store anyway.
- **Reporting unreachable sectors** like unsold items instead of failing. A sector no walkway reaches
  is a broken floor plan (B7 must refuse it), not a property of the list.

## Consequences

- The first plan of a freshly mapped store finds up to n² routes for n stops with A*; later plans
  read them from the route cache (TD-11).
- B4 (#7) stores the assortment as (store, sector, product) with a uniqueness constraint on
  (store, product), and runs the `AssortmentRepository` contract against MySQL.
- B6 (#9) shows `VisitPlan::$unsold`; v1's `codigoSetorOrigem` (re-planning from where the shopper
  stands) is not ported (TD-12).
- `tests/Feature/Golden/StoreOneVisitTest.php` pins both orders on store 1: nearest neighbour gives
  v1's 3, 6, 5, 10, 11 (1,259 steps), and the use case gives 3, 5, 6, 10, 11 (1,193 steps), checked
  against every one of the 120 orders.
