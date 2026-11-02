# Contributing

Thanks for taking the time. The full rules are in
[`.context/docs/development-conventions.md`](.context/docs/development-conventions.md); this page is
the short version.

## Setup

```bash
make up        # Docker: PHP 8.4 (FrankenPHP) and MySQL 8.4
make install   # Composer and npm dependencies, and the git hooks
```

## Branches

Branch from `master`, named by the kind of change:

| Prefix | For |
| --- | --- |
| `feat/` | a new capability |
| `fix/` | a defect |
| `docs/` | documentation only |
| `refactor/` | a structural change with no behaviour change |
| `chore/` | maintenance, dependencies |
| `test/` | tests only |
| `ci/` | workflows |

## Commits

[Conventional Commits](https://www.conventionalcommits.org/), checked by commitlint in the
`commit-msg` hook and, in CI, on every commit of a pull request:

```
fix(visit): stop counting a missing route as length 0

v1 treated a pair of sectors without a stored route as distance 0, so the
planner could send the visit to a sector it had no path to. The planner
now asks the pathfinder and reports an unreachable sector instead.

Closes #6
```

**The commit type is the version**: `feat` → minor; `fix`, `perf`, `refactor` → patch; `!` after the
type or a `BREAKING CHANGE:` footer → major; `docs`, `test`, `build`, `ci`, `chore`, `style` → no
release.

## Before pushing: the local gate

```bash
make gate
```

It runs every CI check, one after the other: Pint, PHP_CodeSniffer, PHPStan, PHPMD, magic numbers,
Rector (dry run), Deptrac, composer-unused, Pest with 80% coverage, `composer audit`, the context
check, ESLint, knip and `npm audit`. If it passes locally, CI passes.

## Pull requests

- Small, one concern each. A refactor and a behaviour change are two pull requests.
- Fill in the template. Verification states **results** ("18/18 tests, coverage 100%"), not
  "tested".
- Link the issue with an English closing keyword: `Closes #12`. Other words do not link.
- Every public class and method has a doc comment; every domain rule has a test.

## Language

Code, comments, docs, commits, issues and pull requests are in English.

## Repository settings (maintainers)

- **Ruleset on `master`**: pull request required, squash merge only, linear history, no force push
  or deletion; required status checks: every job of `.github/workflows/ci.yml` and `CodeQL`.
  Renaming a job breaks its required check: update the ruleset in the same change.
- **Code security**: secret scanning, push protection, Dependabot alerts and security updates,
  private vulnerability reporting (used by [SECURITY.md](SECURITY.md)).
