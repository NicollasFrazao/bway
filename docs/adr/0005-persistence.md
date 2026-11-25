# 0005. Persistence: Eloquent adapters behind the domain ports, constraints that repeat the domain, grids and routes in MySQL

- Status: accepted
- Date: 2026-11-25
- Issue: [#7](https://github.com/NicollasFrazao/bway/issues/7)

## Context

B1 to B3 declared six ports in `app/Domain` (`ProductCatalog`, `ShoppingListRepository`,
`StoreRepository`, `AssortmentRepository`, `StoreGridRepository`, `RouteCache`) and served them, in
tests, with in-memory adapters. The grid and the route cache also had filesystem adapters on the
`local` disk (`stores/<id>/grid.json`, `stores/<id>/routes/<from>-<to>.json`), and
[ADR 0003](0003-floor-plan-mapping-and-routing.md) left open whether B4 would move them to MySQL
(TD-09). The feature tests ran on SQLite in memory while the app runs on MySQL 8.4 (TD-03).

v1 kept its data in MySQL with Portuguese table names (`tb_produto`, `tb_estabelecimento`, `tb_setor`,
`tb_divisoria`, `setor_produto`, `tb_lista_compras`), checked no rule in the schema, and seeded no
store: store 1 existed only in the database of the 2020 team, which is not in the repository.

## Decision

- **One Eloquent adapter per port**, in `app/Infrastructure/Persistence/`: `EloquentProductCatalog`,
  `EloquentShoppingListRepository`, `EloquentStoreRepository`, `EloquentAssortmentRepository`,
  `EloquentStoreGridRepository` and `EloquentRouteCache`. The Eloquent models are named `*Record`
  and live in `Persistence/Eloquent/`; an adapter maps them to domain objects and never returns one,
  so Eloquent stays inside `Infrastructure` (Deptrac). `PersistenceServiceProvider` binds the six
  ports; the other providers no longer bind storage.
- **A row is rebuilt through the domain.** A store is loaded as a `Store` aggregate, which checks its
  layout again; a list item through `ProductName`. A row the domain refuses fails on the way out
  instead of reaching a use case.
- **The schema repeats the domain rules it can**, so a row that would make an aggregate impossible to
  load is refused when it is written:
  - at most one entrance per store, through a virtual generated column (the store id on the
    entrance, `null` on the other sectors) under a unique index;
  - one sector per product per store (`sector_products`: unique store and product), as
    `ProductInSeveralSectors` says;
  - a product once per list (unique list and product), as `DuplicateListItem` says;
  - a sector referenced **together with its store** (composite foreign keys on `(store_id, id)`), so
    the assortment and the routes cannot point to another store's sector;
  - deleting a store deletes its layout, assortment, grid and routes (cascades).

  Tables and columns have English names, integer keys as [ADR 0002](0002-domain-model.md) decided,
  and names at most 100 characters, the domain's limit.
- **A shopping list is one row per owner and one row per item**, with its position and the product
  name it was added with (ADR 0002). `save()` replaces the stored items with the list's in one
  transaction.
- **Grids and routes move to MySQL (TD-09 decided).** A grid is one `store_grids` row: width, height
  and every cell code (0 to 4, v1's) row after row in a `MEDIUMTEXT` (store 1 is 478 × 492, about
  235,000 characters), checked against the width and height when it is read. A route is one `routes`
  row per store and ordered pair of sectors, its cells a JSON list of `[x, y]`; `remember()` is an
  upsert, so two requests caching the same pair leave one row, and `forgetStore()` deletes the
  store's rows. The filesystem adapters are removed; the ports are unchanged.
- **`StoreRepository` stays read-only.** Creating a store is B7's (#10), and the dead-code detector
  refuses a public method nothing calls. The seeders and the tests write stores through
  `Database\Seeders\StoreWriter`, which takes a `Store` aggregate, so a layout the domain refuses
  never reaches the tables.
- **Seed data makes the whole flow run on a fresh clone:** v1's catalogue in English (212 products,
  202 after merging the ten v1 listed twice); store 1 from v1's fixtures (the sector rectangles and
  partitions of its mapping, sector 2 as the entrance, as in the golden tests; the sector names are
  the seed's, since v1's database is lost), its assortment, and v1's floor plan mapped by the
  `MapFloorPlan` use case; a demo user `test@example.com` with a list that needs every sales sector
  and one product store 1 does not sell. The app container runs `migrate --seed` on every start; the
  seeder does nothing once store 1 exists.
- **The feature tests run on MySQL 8.4 (TD-03 closed)**, the image of `docker-compose.yml`, in a
  `bway_testing` database (`phpunit.xml`) that MySQL creates when its volume is new
  (`docker/mysql/initdb/`), so a test run never wipes the app's data. In CI the `test` job runs the
  same image as a service. The port contracts moved to `tests/Feature/Contracts/` and run against
  the in-memory and the Eloquent adapter of each port.
- **One PHPStan entry is ignored, with its reason in `phpstan.neon.dist`:** Larastan reports every
  `Query\Builder` method an Eloquent builder forwards (`orderBy`, `whereIn`, `delete`...) as static
  (`EloquentBuilderMethodReflection::isStatic()`), so strict-rules reads each call as
  `staticMethod.dynamicCall`. Only that message, on the Eloquent builder, is ignored.

## Alternatives considered

- **Keeping grids and routes on disk.** Forgetting a store's routes is one directory delete, but a
  store would live in two places: a database backup would not hold its grid, deleting a store would
  leave its files, and nothing would stop a route file from naming a sector the store does not have.
  As rows, the cascades and the composite keys give all three for free.
- **A grid as a JSON array of rows, or one row per cell.** JSON spends a comma and quotes on every
  cell; one row per cell is about 235,000 rows per store, read back on every route search. One
  string of codes is the format the domain already keeps each row in (ADR 0003).
- **SQLite for the tests**, as before. It is faster to start, but the entrance's generated column is
  written in MySQL's SQL (`if()`), and the point of B4's tests is the schema and the queries
  production runs; a test on SQLite would prove another database.
- **A `save()` on `StoreRepository` now.** Nothing in B4 creates a store except the seed; the method
  would be dead code until B7, which will shape it around store creation.
- **Seeding v1's sector names and entrance.** They were in v1's database, not in its code or
  fixtures; inventing them as "v1's" would claim a fidelity the seed does not have.

## Consequences

- The ports are tested against MySQL; a test that boots Laravel needs the database running
  (`make up`). The unit tests do not.
- A local MySQL volume created before B4 has no `bway_testing` database: `docker compose down -v`
  once.
- `MapFloorPlan` saves the new grid and then forgets the store's routes in two statements, outside one
  transaction: if the second fails, the routes found on the old grid are still served (TD-13).
- The B2 and B3 use cases now run against real storage, but no route calls them yet (TD-01: B5, #8;
  B6, #9).
- [ADR 0003](0003-floor-plan-mapping-and-routing.md)'s files per store are replaced by rows; its
  cache per ordered pair, and forgetting a store's routes on a new mapping, stay.
