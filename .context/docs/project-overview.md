---
type: doc
name: project-overview
description: What bway does, what the 2020 version was, and what the v2 rewrite delivers in which phases
category: overview
generated: 2026-11-01
status: filled
scaffoldVersion: "2.0.0"
---

# Project overview

bway turns a shopping list into a supermarket visit ordered by sector. The user builds a list from a
product catalogue, picks a store, and the app groups the list by the sectors of that store and orders
them so the walk through the store is short, using routes computed with A* over the store's floor
plan.

## Origin: v1 (2020)

bway was the final project (TCC) of a Computer Engineering degree at Universidade Santa Cecília, in
2020, by a team of four; the paper is in [`docs/paper/`](/docs/paper/) (Portuguese). The code
submitted then is frozen as the **`v1` tag** and the **`legacy/v1` branch**:

- Laravel 7 with Blade views and AngularJS 1.8 controllers inline in each view;
- a separate Python/Flask service that read a floor-plan image, classified its pixels by colour
  into a grid (free floor, wall, sector, partition) and ran A* between the centres of two sectors,
  storing each route as a JSON file;
- a greedy ordering of the sectors in the cart: from the entrance, repeatedly the nearest sector by
  stored route length.

What v1 left undone or wrong, and v2 fixes: no seed data for stores, the store hard-coded to `1`,
store creation not implemented, plain-text passwords, user ids taken from the URL, a pair of sectors
without a stored route counted as length 0, the mapping file written under one name and read
under another, a search that ranked cells by straight-line distance from the start instead of steps
walked (3 of store 1's 30 routes 26 steps longer than the shortest), and a mapping that left the
right-hand wall open and drew a wall across one row. The paper also describes a React Native client and BLE beacons in each sector; they
were never in this repository and stay out of scope.

## v2: the rewrite

One Laravel 13 / PHP 8.4 application on MySQL 8.4, in Docker. The floor-plan mapping and the A*
routing move from Python into PHP domain classes, so there is one service to run. The code is layered
(`Domain`, `Application`, `Infrastructure`, `Http`) with the rules enforced in CI; see
[architecture.md](architecture.md).

The plan is the umbrella issue [#2](https://github.com/NicollasFrazao/bway/issues/2), in phases:

| Phase | Issue | Delivers |
| --- | --- | --- |
| B0 | #3 | v1 tag, Laravel 13 skeleton, Docker, quality gate and CI |
| B0.1 | #19 | Engineering standards: this context base, contribution rules, guard rails, doc comments |
| B1 | #4 | Domain model: shopping list, products, stores, sectors and partitions |
| B2 | #5 | Floor-plan mapping and A* routing in PHP, golden-tested against v1's mapping and routes for store 1 |
| B3 | #6 | Visit planner: order the sectors of a store visit |
| B4 | #7 | Persistence: migrations, repositories and seed data |
| B5 | #8 | Accounts and authorisation |
| B6 | #9 | Web interface: list, visit and map |
| B7 | #10 | Store management: create a store from its floor plan |
| B8 | #11 | Release: documentation, decision records and `v2.0.0` |

Order: B0 → B0.1 → B1 → B2 → B3 → B4 → (B5, B6, B7) → B8.

## Current state

B0 to B3: the skeleton serves Laravel's welcome page and every check of the quality gate runs in
CI. `app/Domain` holds the domain model (shopping list, catalogue, stores with their sectors,
partitions and assortment, grids), the floor-plan mapping and the A* routing (B2), and the visit
planner (B3: the list grouped by sector, unsold items reported, the sectors ordered from the
entrance by nearest neighbour improved by 2-opt, on real route lengths), unit-tested without
Laravel and golden-tested against v1's store 1. The use cases run only in tests: persistence is B4
and nothing uses them from a route yet.

## Out of scope

- The React Native client and the BLE beacons of the paper.
- Indoor positioning of any kind: the visit starts at the entrance sector, as in v1.
