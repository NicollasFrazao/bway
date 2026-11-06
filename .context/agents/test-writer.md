---
type: agent
name: Test Writer
description: Write Pest unit and feature tests, routing golden tests, and keep coverage at or above 80%
agentType: test-writer
phases: [E, V]
generated: 2026-11-01
status: filled
scaffoldVersion: "2.0.0"
---

# Test writer

Read [testing-strategy.md](../docs/testing-strategy.md) first.

## Rules

- Unit tests (`tests/Unit/`) never boot Laravel: build the objects with `new`, pass fakes for ports.
  If that is impossible, the class under test is in the wrong layer.
- Feature tests (`tests/Feature/`) use the namespaced Pest Laravel functions (`Pest\Laravel\get`)
  and the `command()` helper of `tests/Pest.php`, never `$this->property` state: PHPStan at max
  cannot type it.
- Name tests as sentences describing behaviour; one behaviour per test; datasets (`->with([...])`)
  for the same behaviour over several inputs.
- Temporary files go through a helper that cleans up after each test (`tests/Support/ScratchRepository.php`).

## Patterns in this repository

- `tests/Unit/Console/Context/SurfaceDifferenceTest.php` — a pure function tested by its edge cases.
- `tests/Unit/Console/Context/ContextSurfacesTest.php` — a class with a filesystem collaborator, on a
  scratch directory.
- `tests/Feature/Console/ContextCommandsTest.php` — Artisan commands, success and failure paths, on
  the real repository and on a scratch one.

## Routing golden tests (B2)

The v1 floor plan, mapping and routes of store 1 are in `tests/Fixtures/v1/store-1/`, read through
`tests/Support/V1StoreOne.php`; `tests/Feature/Golden/StoreOneTest.php` asserts the PHP mapper and
pathfinder give the same grid and the same cells, except the differences it lists by name with their
cause ([ADR 0003](../../docs/adr/0003-floor-plan-mapping-and-routing.md)). A change to the mapping or
the search that moves a route must either keep those tests green or add the pair to the list with an
assertion that explains it — never loosen the comparison.

## Done when

`composer test:coverage` passes (80% line coverage of `app/`), and PHPStan and PHP_CodeSniffer pass
on the tests too.
