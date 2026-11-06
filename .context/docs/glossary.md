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
| **floor plan** | The image of a store's layout that the mapping reads. | `planta` |
| **mapping** | Turning a floor plan into a grid, by classifying each pixel's colour. | `mapeamento`, `mapearPlanta` |
| **grid** | The mapped store: a matrix of cells, each free floor, wall, sector or partition. | `mapeamento.json` (`0` floor, `1` wall, `2` sector, `3` partition, `4` route) |
| **cell** | One position of the grid; addressed by a `GridCoordinate` (x, y), both from 0, y counting rows from the top. | — |
| **grid area** | A rectangle of cells (`GridArea`): its top-left cell, a width and a height of at least one cell. The floor of a sector, the footprint of a partition. | `vl_x`, `vl_y`, `vl_largura`, `vl_comprimento` |
| **sector** | An area of the store where a group of products is sold (a rectangle on the grid). | `Setor`, `tb_setor` |
| **entrance** | The sector a visit starts from. A store has exactly one (`StoreWithoutEntrance`, `StoreWithSeveralEntrances`). | `ic_entrada` |
| **sector kind** | What a sector is for: `Entrance` or `Sales` (`SectorKind`). | `ic_entrada` true / false |
| **partition** | A shelf or divider inside the store; it blocks movement. | `Divisoria`, `tb_divisoria` |
| **sector product** | The fact that a store sells a product in a given sector. | `setor_produto` |
| **route** | A walkable path of cells between two sectors, with its length in cells. | `Rota`, `tb_rota`, `gerarRota` |
| **route length** | The number of steps of a route. v1 counted a missing route as 0; v2 never does. | — |
| **pathfinder** | The search that finds a route on the grid; `AStarPathfinder` is the implementation. | `gerarRota` (Python) |
| **heuristic** | The distance estimate A* uses; Manhattan distance, as in v1. | — |
| **visit** | One trip through a store to buy the items of a list: the sectors to walk through, in order. | `Carrinho`, `tb_carrinho` |
| **visit planner** | The domain service that orders the sectors of a visit. | the ordering in `CarrinhoController` |
| **ordering strategy** | How the planner chooses the next sector; the first one is nearest neighbour from the entrance (v1's behaviour). | — |
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
| **composition root** | `AppServiceProvider`, the only place that binds ports to adapters. |
| **context index** | `.context/docs/README.md`, the source of the agent surfaces. |
| **agent surface** | `AGENTS.md`, `.claude/CLAUDE.md`, `.github/copilot-instructions.md`: copies of the context index. |
| **gate** | The set of checks in [development-conventions.md](development-conventions.md); every one is a CI job. |

## Abbreviations allowed in names

`id`, `db`, `dto`, `http`, `url`, `uri`, `sql`, `json`, `csv`, `x` and `y` (grid coordinates), `a`
and `b` only as the two operands of a comparison. Nothing else: write the word.
