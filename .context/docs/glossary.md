---
type: doc
name: glossary
description: Domain vocabulary, the Portuguese names v1 used for each term, and the allowed abbreviations
category: glossary
generated: 2026-11-01
status: filled
scaffoldVersion: "2.0.0"
---

# Glossary

Use the English term in code, tests, logs and docs, with this meaning. The v1 column is what the
2020 code (tag `v1`) called it, to read that code and its database; never use it in new code.

## Domain

| Term | Meaning | v1 (Portuguese) |
| --- | --- | --- |
| **user** | A person with an account who keeps a shopping list. | `Usuario`, `tb_usuario` |
| **product** | An item of the catalogue, known by its name (`ProductName`) and identified by its `ProductId`. | `Produto`, `tb_produto` |
| **catalogue** | Every product a user can add to a list (`ProductCatalog` is its repository). | — |
| **shopping list** | The products a user intends to buy; no product appears twice (`DuplicateListItem`). Aggregate root; a user has one, identified by its owner. | `ListaCompras`, `tb_lista_compras` |
| **list item** | One product in a shopping list: its id and the name it had when added. | row of `tb_lista_compras` |
| **acquire** | Mark a list item as bought during a visit; it leaves the list. | `adquirir`, `ic_adquirido` |
| **store** | A supermarket with a floor plan, sectors and partitions. | `Estabelecimento`, `tb_estabelecimento` |
| **floor plan** | The image of a store's layout that the mapping reads (JPEG, PNG, GIF or WebP), drawn in the legend. | `planta` |
| **legend** | The colours a floor plan is drawn in: red wall, blue sector outline, green partition, orange walkway, anything else free floor (`LegendColorClassifier`, with v1's tolerances). | — |
| **wall frame** | The rectangle of wall around the store on the floor plan; the mapping keeps only what it encloses, the frame included. | `extremidade` |
| **mapping** | Turning a floor plan into a grid, by classifying each pixel's colour (`FloorPlanMapper`). | `mapeamento`, `mapearPlanta` |
| **grid** | The mapped store: a rectangle of cells, each of one cell kind (`Grid`). | `mapeamento.json`, `ds_mapeamento` |
| **cell kind** | What a cell is (`CellKind`): free floor, wall, sector outline, partition or walkway, coded 0 to 4 as in v1. | `0` floor, `1` wall, `2` sector, `3` partition, `4` route |
| **walkway** | An aisle painted orange on the floor plan; the only kind of cell a route may enter. | `4` ("Rota") |
| **cell** | One position of the grid; addressed by a `GridCoordinate` (x, y), both from 0, y counting rows from the top. | — |
| **grid area** | A rectangle of cells (`GridArea`): its top-left cell, a width and a height of at least one cell. The floor of a sector, the footprint of a partition. | `vl_x`, `vl_y`, `vl_largura`, `vl_comprimento` |
| **sector** | An area of the store where a group of products is sold (a rectangle on the grid). Sectors and partitions never share a cell (`OverlappingLayout`) and must fit the grid (`LayoutOutsideGrid`). | `Setor`, `tb_setor` |
| **centre** (of a sector) | The cell routes to and from a sector start and end on: the area's origin plus half each side, rounded down (`GridArea::center()`). | `int(vl_x + vl_largura / 2)` |
| **entrance** | The sector a visit starts from. A store has exactly one (`StoreWithoutEntrance`, `StoreWithSeveralEntrances`). | `ic_entrada` |
| **sector kind** | What a sector is for: `Entrance` or `Sales` (`SectorKind`). | `ic_entrada` true / false |
| **partition** | A shelf or divider inside the store; it blocks movement. | `Divisoria`, `tb_divisoria` |
| **sector product** | The fact that a store sells a product in a given sector (`SectorProduct`). | row of `setor_produto` |
| **assortment** | Which products each sector of a store sells (`Assortment`, loaded by `AssortmentRepository`); a product is sold in at most one sector of a store (`ProductInSeveralSectors`). v1 allowed several and used the first by sector name. | `setor_produto` |
| **unsold item** | A list item no sector of the chosen store sells; the plan reports it (`VisitPlan::$unsold`). v1 dropped it silently. | — |
| **route** | The cells walked from a start to a target, both included, each one step (beside, above or below) from the last (`Route`). | `Rota`, `tb_rota`, `gerarRota` |
| **route length** | The number of steps of a route: its cells minus one, 0 from a sector to itself. v1 counted the entries of its route file (steps + 1) and a missing route as 0; v2 reports a missing route as `RouteNotFound`. | — |
| **route cache** | The route of each ordered pair of sectors of a store, found once and kept (`RouteCache`); mapping the store again forgets them. | `rotas/<store>-<from>-<to>.json`, `tb_rota` |
| **pathfinder** | The search that finds a shortest route on the grid; `AStarPathfinder` is the implementation. | `gerarRota` (Python) |
| **heuristic** | The distance estimate A* uses (`Heuristic`); Manhattan distance (`ManhattanDistance`), as in v1. | — |
| **visit** | One trip through a store to buy the items of a list: the sectors to walk through, in order. | `Carrinho`, `tb_carrinho` |
| **visit plan** | The planned visit (`VisitPlan`): the start (the entrance), the stops in order, the unsold items and the length in steps. Computed from the list each time, never stored. | the JSON of `CarrinhoController::show` |
| **stop** | One sector of a visit with the list items picked up there (`VisitStop`). | an entry of the cart |
| **visit planner** | The domain service that groups a list by sector and orders the sectors from the entrance (`VisitPlanner`, used by the `PlanVisit` use case). | the ordering in `CarrinhoController` |
| **route lengths** | The port that gives the steps between two sectors of a store (`RouteLengths`); its adapter goes through the route cache. | `count(RotaController::show())` |
| **distance table** | The route lengths of one visit, measured before ordering: from the start and every stop to every other stop (`DistanceTable`). | — |
| **ordering strategy** | How the planner orders the stops (`VisitOrderingStrategy`). | — |
| **nearest neighbour** | The ordering strategy that always walks to the nearest stop not visited yet (`NearestNeighbourOrdering`); v1's behaviour. | the `while` loop of `CarrinhoController::show` |
| **2-opt** | The ordering strategy that improves a proposed order by reversing runs of stops while that shortens the walk (`TwoOptOrdering`); bound in production over nearest neighbour. | — |
| **beacon** | A BLE device per sector in the paper's design. Out of scope; v1 kept only a `ds_beacon` column. | `ds_beacon` |

## Code

| Term | Meaning |
| --- | --- |
| **identifier** | The identity of an entity: a positive integer the storage assigns, one class per entity (`ProductId`, `StoreId`, `SectorId`, `PartitionId`, `UserId`). |
| **name** | Text people read: trimmed, single-spaced, 1 to 100 characters (`Name`, and `ProductName` for products). |
| **domain error** | An exception for a broken domain rule; every one extends `DomainError` and is named for what went wrong. |
| **contract test** | A test of a port that every adapter of it must pass; the in-memory adapters in `tests/Support/Domain/` are the reference. |
| **port** | An interface declared in `Domain` (usually a repository) and implemented in `Infrastructure`. |
| **adapter** | An implementation of a port. |
| **use case** | One operation of the application, a class in `app/Application/`. |
| **composition root** | The providers in `app/Providers/` (`AppServiceProvider`, `VisitServiceProvider`), the only place that binds ports to adapters. |
| **context index** | `.context/docs/README.md`, the source of the agent surfaces. |
| **agent surface** | `AGENTS.md`, `.claude/CLAUDE.md`, `.github/copilot-instructions.md`: copies of the context index. |
| **gate** | The set of checks in [development-conventions.md](development-conventions.md); every one is a CI job. |

## Abbreviations allowed in names

`id`, `db`, `dto`, `http`, `url`, `uri`, `sql`, `json`, `csv`, `x` and `y` (grid coordinates), `a`
and `b` only as the two operands of a comparison. Nothing else: write the word.
