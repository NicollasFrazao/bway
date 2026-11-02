# 0001. Layered architecture: Domain, Application, Infrastructure, Http

- Status: accepted
- Date: 2026-11-01
- Issue: [#3](https://github.com/NicollasFrazao/bway/issues/3)

## Context

In v1 (tag `v1`) the rules of the problem were spread across Eloquent models, controllers, the
AngularJS code inline in the Blade views and a separate Python service. The ordering of the visit
lived in `CarrinhoController`, mixed with queries; the A* search lived in Python, reachable only over
HTTP and tested by nobody. None of it could be tested without a database and a running service.

The rewrite ports the mapping and the routing into PHP (B2) and has to prove they produce the same
routes as v1. That proof, and the tests of every list and visit rule, have to be fast and
independent of Laravel, MySQL and the filesystem.

## Decision

Organise `app/` in four layers, with dependencies pointing inwards only:

| Layer | Holds | May depend on |
| --- | --- | --- |
| `app/Domain` | entities, value objects, domain services, repository interfaces, domain exceptions | nothing outside itself (PHP's own classes allowed) |
| `app/Application` | use cases and their DTOs | `Domain` |
| `app/Infrastructure` | Eloquent models and repositories, file storage, image reading, jobs | `Domain`, `Application`, Laravel and other packages |
| `app/Http` | controllers, form requests, resources | `Domain`, `Application`, Laravel |

`app/Providers` is the composition root and may see every layer; `app/Console` holds developer
tooling and depends only on the framework. `Http` and `Infrastructure` never depend on each other.

The rules are enforced, not documented only: `deptrac.yaml` declares the layers, a `Vendor` layer
holds every third-party namespace, and the `deptrac` CI job fails on any violation and on any
dependency Deptrac cannot classify (`--fail-on-uncovered`).

## Alternatives considered

- **Laravel's default layout** (models, controllers, services in `app/`): the fastest start, but
  nothing stops a domain rule from calling Eloquent, which is how v1 ended up untestable.
- **Modules per feature** (`app/ShoppingList/{Domain,Http,...}`): good for a large app, but the
  domain here is small and highly connected (the visit needs the list, the store and the routes);
  module boundaries would be drawn through the middle of it.
- **A separate Composer package for the domain**: the strongest isolation, at the cost of a second
  package, its own autoloading and versioning, for one application.

## Consequences

- Domain and use-case tests run without the framework (`tests/Unit/` does not boot Laravel), so the
  routing golden tests and the visit tests take milliseconds.
- Every persistence need costs an interface in `Domain` and a mapping in `Infrastructure`, more code
  than using Eloquent directly. Accepted: the mapping is where v1's schema mistakes get fixed.
- A new package used from `app/` must be added to the `Vendor` layer before Deptrac passes; the
  friction is deliberate, it makes each new dependency visible in review.
- Laravel features that assume models in `app/Models` (factory discovery, policies by convention)
  need explicit configuration, as `#[UseFactory]` on the user model already shows.
