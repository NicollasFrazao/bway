# Architecture decision records

One file per decision that shaped the code and would be expensive to reverse. Each says what was
decided, what else was considered, and what it costs. A superseded record stays, marked as such.

| # | Decision | Status |
| --- | --- | --- |
| [0001](0001-layered-architecture.md) | Layered architecture: Domain, Application, Infrastructure, Http, enforced by Deptrac | Accepted |
| [0002](0002-domain-model.md) | Domain model: one list per user, integer identities, the entrance as a kind of sector | Accepted |
| [0003](0003-floor-plan-mapping-and-routing.md) | Floor-plan mapping and routing: v1's legend, a true A* on walkways, routes cached per pair | Accepted |
