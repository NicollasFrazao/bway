# 0002. Domain model: one list per user, integer identities, the entrance as a kind of sector

- Status: accepted
- Date: 2026-11-05
- Issue: [#4](https://github.com/NicollasFrazao/bway/issues/4)

## Context

B1 models the shopping list, the catalogue and the store layout as plain PHP in `app/Domain`, to
replace the Eloquent models `Usuario`, `Produto`, `Estabelecimento`, `Setor`, `Divisoria` and
`Carrinho` and the rules spread across `ListaComprasController` and `CarrinhoController` in v1. A
few choices shape every later phase (routing, visit, persistence) and are recorded here.

## Decision

- **Contexts.** `app/Domain` has one folder per part of the domain: `Shared` (identities, names,
  the `DomainError` base), `Catalog` (products), `ShoppingList`, `Store` (sectors and partitions)
  and `Grid` (coordinates and areas, which B2's mapping and routing extend).
- **Identities are positive integers wrapped in one class per entity** (`ProductId`, `StoreId`,
  `SectorId`, `PartitionId`, `UserId`, all extending `Identifier`). The storage assigns them, as
  v1's auto-increment keys did and B4's MySQL tables will; the domain never invents one. The
  separate classes stop a sector id from being passed as a product id.
- **One shopping list per user, identified by its owner.** v1 kept the list as the pairs
  (user, product) of `tb_lista_compras`; the aggregate keeps that shape, so it needs no id of its own
  and `ShoppingListRepository::forOwner()` returns an empty list for a user who has none.
- **A list item refers to its product by id and keeps the product's name**, so a list can be shown
  without a catalogue lookup. Items keep the order they were added in; v1's alphabetical order was a
  query (`orderBy('nm_produto')`), and ordering for display stays a reader's concern.
- **Acquiring an item removes it from the list**, as `CarrinhoController::update` did; removing or
  acquiring a product that is not on the list is an error (`ProductNotInList`), where v1 detached
  silently. The visit that records what was acquired is B3's; B3 decided in
  [ADR 0004](0004-visit-planning.md) that a visit is planned from the list each time, not stored.
- **The entrance is a kind of sector** (`SectorKind::Entrance`), as v1's `ic_entrada` flag was, and
  `Store` refuses at construction a layout with no entrance or with several. An enum, not a boolean:
  a boolean argument hides its meaning at the call site (PHPMD `BooleanArgumentFlag`).
- **Names are trimmed, single-spaced, non-blank and at most 100 characters** (v1's column size),
  in one `Name` value object; `ProductName` applies the same rules under its own type.
- **No `Money`.** v1 stores and shows no prices, and nothing in the rewrite's plan does either. It
  is added with the first feature that shows a price.
- **Domain errors extend `DomainError`** (a `RuntimeException`), are named for what went wrong and
  built by named constructors, so `Http` can map all of them to one kind of response.

Left to the phase that needs them: which products each sector sells (B3, the visit), the grid's size
and whether sectors and partitions fit inside it or overlap (B2, the mapping). B2 decided the latter in
[ADR 0003](0003-floor-plan-mapping-and-routing.md): parts may not overlap, and must fit the grid. B3
decided the former in [ADR 0004](0004-visit-planning.md): an `Assortment` of the store, one sector
per product.

## Alternatives considered

- **UUIDs generated in the domain**: let an aggregate have an identity before it is stored, at the
  cost of a second key next to v1's integers and of larger indexes. Nothing in B1-B4 creates an
  aggregate whose id is needed before saving.
- **A boolean `isEntrance` constructor argument**, or the entrance as a separate `Store` property:
  the first is the flag PHPMD rejects; the second makes "exactly one entrance" true by construction
  but loses v1's shape (the entrance is a sector with an area like any other) and moves the check
  into every mapper.
- **Silently ignoring a remove of a product not on the list**, as v1 did: idempotent for a double
  click, but it hides a stale screen; the outer layer can still choose to treat the error as success.

## Consequences

- Ports are tested by contract: `tests/Unit/Domain/*/*RepositoryTest.php` and `ProductCatalogTest.php`
  run against in-memory adapters (`tests/Support/Domain/`); B4 runs the same contract against the
  Eloquent adapters (`TODO(#7)` in each file).
- B4's schema keeps integer keys and the (user, product) shape of the list, with a uniqueness
  constraint mirroring `DuplicateListItem`.
- A store layout that breaks a rule cannot be loaded at all; B4's seed data and B7's store creation
  must produce exactly one entrance.
