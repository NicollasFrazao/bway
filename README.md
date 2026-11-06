# bway

Shopping-list app that turns a user's list into a supermarket visit ordered by sector, using routes
computed with A* over a mapped floor plan.

> **v2 is being rewritten** on this branch in Laravel 13 with a layered architecture; the plan and
> its phases are in [#2](https://github.com/NicollasFrazao/bway/issues/2). The 2020 version is
> frozen as [`v1`](#v1-the-2020-final-project).

## Status

Phases B0 to B3: a Laravel 13 (PHP 8.4) skeleton on Docker with the full quality gate in CI, and
the domain — shopping list, stores and their sectors, floor-plan mapping, A* routing and visit
planning — as plain PHP with its use cases, tested without the framework. Persistence and the
screens arrive in the next phases.

## Running locally

Requirements: Docker with Compose v2; Node.js 22 for the git hooks and the JavaScript checks.

```bash
make up        # app on http://localhost:8000 and MySQL 8.4; installs dependencies and migrates on first run
make install   # Composer and npm dependencies and the git hooks
make gate      # every check CI runs
```

`make help` lists the other targets (`test`, `coverage`, `lint`, `analyse`, `sh`, `down`...).

## Architecture

`app/` is split into `Domain` (no framework), `Application` (use cases), `Infrastructure` (Eloquent,
storage) and `Http`, with the dependency rules enforced by Deptrac in CI. See
[ADR 0001](docs/adr/0001-layered-architecture.md) and
[`.context/docs/architecture.md`](.context/docs/architecture.md).

## Quality gate

Every check is its own CI job and a required status check: Pint, PHP_CodeSniffer (doc comments and
readability), PHPStan at max (Larastan, strict rules, cognitive complexity ≤ 15, dead code), PHPMD,
magic numbers, Rector (dry run), Deptrac, composer-unused, Pest with 80% coverage, `composer audit`,
ESLint (with the Sonar rules), knip, `npm audit`, a Docker smoke test, the context surfaces check,
commitlint and CodeQL. Details in [`.context/docs/tooling.md`](.context/docs/tooling.md).

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md). The knowledge base for people and coding agents starts at
[`.context/docs/README.md`](.context/docs/README.md).

## v1: the 2020 final project

bway was the final project (TCC) of the Computer Engineering degree at Universidade Santa Cecília,
2020, by Lucas Henrique dos Santos, Marcus Vinicius Alves Rodrigues dos Santos, Nícollas Frazão and
Pedro Marcos Gonçalves de Araujo Diegues Pinto. The code was written by Nícollas Frazão; the paper
([`docs/paper/`](docs/paper/), in Portuguese) is the team's.

v1 was a Laravel 7 app with AngularJS screens and a Python/Flask service that mapped the floor plan
and ran the A* search. It is kept exactly as submitted:

- tag [`v1`](https://github.com/NicollasFrazao/bway/tree/v1) — its README describes what it does,
  how to run it and its known limitations;
- branch [`legacy/v1`](https://github.com/NicollasFrazao/bway/tree/legacy/v1), the same commit.

## License

The code is under the [MIT License](LICENSE). The paper in [`docs/paper/`](docs/paper/) is the
team's work and is not covered by it.
