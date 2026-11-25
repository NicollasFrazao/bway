# Architecture decision records

One file per decision that shaped the code and would be expensive to reverse. Each says what was
decided, what else was considered, and what it costs. A superseded record stays, marked as such.

| # | Decision | Status |
| --- | --- | --- |
| [0001](0001-layered-architecture.md) | Layered architecture: Domain, Application, Infrastructure, Http, enforced by Deptrac | Accepted |
| [0002](0002-domain-model.md) | Domain model: one list per user, integer identities, the entrance as a kind of sector | Accepted |
| [0003](0003-floor-plan-mapping-and-routing.md) | Floor-plan mapping and routing: v1's legend, a true A* on walkways, routes cached per pair | Accepted |
| [0004](0004-visit-planning.md) | Visit planning: one sector per product, lengths measured up front, nearest neighbour improved by 2-opt | Accepted |
| [0005](0005-persistence.md) | Persistence: Eloquent adapters behind the domain ports, constraints that repeat the domain, grids and routes in MySQL | Accepted |
| [0006](0006-accounts-and-authorisation.md) | Accounts and authorisation: the account in the domain, the owner from the session, a gate for store management | Accepted |
| [0007](0007-web-interface.md) | Web interface: server-rendered Blade pages, the map drawn as SVG from the store's layout and its routes | Accepted |
