---
type: doc
name: testing-strategy
description: Unit and feature tests with Pest, the coverage gate, and what each phase must test
category: testing
generated: 2026-11-01
status: filled
scaffoldVersion: "2.0.0"
---

# Testing strategy

Tests are written with [Pest](https://pestphp.com) 4 on PHPUnit 12. `composer test:coverage` (the
`test` CI job) runs them all with pcov and fails under **80% line coverage of `app/`**.

## Layers

| Suite | Folder | Boots Laravel | For |
| --- | --- | --- | --- |
| Unit | `tests/Unit/` | **no** | Domain and application classes, and any class that takes its collaborators by constructor |
| Feature | `tests/Feature/` | yes (`Tests\TestCase`) | Routes, Artisan commands, repositories, the wiring of the provider |

`tests/Pest.php` binds `Tests\TestCase` to `Feature` only. Keep it that way: a domain test that
needs the container is a sign the class reaches outside the domain.

## Database

Feature tests run on SQLite in memory with array cache, session and mail drivers (`phpunit.xml`).
The `.env` of the Docker stack does not leak in: `phpunit.xml` sets the variables first, and
Laravel's environment loader never overwrites a variable that is already set.

From B4 (#7) on, the repository tests run against MySQL 8.4 (the same image as `docker-compose.yml`,
as a service in the CI job), because the queries and constraints are MySQL's.

## Conventions

- One behaviour per test, named as a sentence: `it('reports a surface edited by hand and a missing
  one')`.
- Arrange, act, assert, separated by blank lines when the test is longer than three lines.
- No `$this->property` state in Pest closures: PHPStan cannot type it. Use a typed helper instead
  (`tests/Support/ScratchRepository.php` is the model) and the namespaced Laravel functions
  (`Pest\Laravel\get`, the `command()` helper in `tests/Pest.php`).
- Files the test writes go under the system temp directory, through a helper that deletes them after
  each test (`ScratchRepository::deleteAll()` in `tests/Pest.php`).
- Literal numbers and strings are fine in tests: they are the readable form of a case. Tests are
  exempt from the magic-number check, not from PHPStan or PHP_CodeSniffer.

## What each phase must test

| Phase | Tests |
| --- | --- |
| B1 domain | Every invariant: no duplicate product in a list, acquiring removes an item, exactly one entrance per store; every domain exception |
| B2 routing | Golden tests: the mapping and routes of store 1 committed in `v1` (`storage/app/public/estabelecimentos/`) are fixtures, and the PHP output must match; unreachable target, start equals target, walls and partitions block movement |
| B3 visit | Deterministic order on small hand-made grids; products not sold in the store are reported |
| B4 persistence | Each repository against MySQL; the seeded store supports the whole flow |
| B5 accounts | Access to another user's list is denied; rate limiting; CSRF |
| B6, B7 | Feature tests of the main flows and of the admin flow and its queued job |

## Today

21 tests: the welcome page and the health route, the `context:*` commands and their classes, and
the user model (password hashing, hidden attributes, the seeder). They cover 100% of the lines of
`app/`.
