---
type: doc
name: development-conventions
description: Normative conventions — quality gate, layer rules, doc comments, readability, commits, branches, PRs, language
category: workflow
generated: 2026-11-01
status: filled
scaffoldVersion: "2.0.0"
---

# Development conventions

What is mandatory when changing this repository. Setup and step-by-step recipes are in
[development-workflow.md](development-workflow.md); this file holds only what must hold without
anything prompting it, written as commands.

## Quality gate

**Before every push, run the gate** (`make up` first; PHP runs in the container):

```bash
make gate
```

It runs, one after the other, exactly the CI jobs:

| CI job | Command | Catches |
| --- | --- | --- |
| `pint` | `composer lint:pint` | formatting (Laravel preset + strict types, strict comparison) |
| `phpcs` | `composer lint:phpcs` | missing or malformed doc comments, nesting, multi-line conditions, commented-out code, a `TODO` without an issue number |
| `phpstan` | `composer analyse:phpstan` | Larastan at `max` + strict rules, cognitive complexity, dead code |
| `phpmd` | `composer analyse:phpmd` | unused code, size and complexity limits, more than 4 parameters |
| `magic-numbers` | `composer analyse:magic-numbers` | numeric literals in `app/` and `routes/` |
| `rector` | `composer analyse:rector` | any change Rector would make (dead code, PHP 8.4 idioms, types) |
| `deptrac` | `composer check:architecture` | a dependency that breaks the layer rules |
| `unused` | `composer check:unused` | Composer packages nothing uses |
| `test` | `composer test:coverage` | failing tests, line coverage of `app/` under 80% |
| `audit` | `composer audit` | Composer packages with known vulnerabilities |
| `context` | `composer check:context` | an agent surface that is not a copy of the context index |
| `eslint` | `npm run lint` | JavaScript lint, Sonar rules included, `--max-warnings 0` |
| `knip` | `npm run check:unused` | unused JavaScript files, exports and npm dependencies |
| `npm-audit` | `npm run audit` | npm packages with known high or critical vulnerabilities |
| `docker` | `docker compose up --wait` + `curl` | the stack does not start or the welcome page does not answer |
| `commitlint` | (pull requests only) | a commit that is not a Conventional Commit |

Every one of them is a required check on `master`. A green local gate means a green CI.

- Run heavy commands **one at a time**: PHPStan and the coverage run each take hundreds of MB.
- The `pre-commit` hook runs Pint, PHP_CodeSniffer and ESLint on the staged files, and `commit-msg`
  runs commitlint. They are a fast first filter, not the gate.
- **A rule is fixed in the code, never silenced.** No `@phpstan-ignore`, `phpcs:ignore`,
  `@SuppressWarnings`, baseline file or `eslint-disable` without a comment on the same line saying
  why the rule does not apply there, and the reviewer has to agree. No file-level or blanket
  disables, and no new entries in the tools' exclude lists without saying why in the config.

## Layer rules

Enforced by Deptrac (`deptrac.yaml`). The reasoning is in [architecture.md](architecture.md) and
[ADR 0001](/docs/adr/0001-layered-architecture.md).

- `app/Domain` depends on **nothing** outside `app/Domain`: no Laravel, no Carbon, no other layer.
  PHP's own classes (`DateTimeImmutable`, `InvalidArgumentException`) are allowed. A rule that needs
  I/O is not a domain rule.
- `app/Application` depends only on `app/Domain`. It reaches storage through the repository
  interfaces declared in `Domain`.
- `app/Http` and `app/Infrastructure` never depend on each other. They meet in
  `app/Providers/AppServiceProvider.php`, the composition root.
- Eloquent models live in `app/Infrastructure/Persistence/` and never cross into `Domain` or
  `Application`: a repository maps them to domain objects.
- A new Composer package used from `app/` is added to the `Vendor` layer in `deptrac.yaml` in the
  same change; otherwise Deptrac reports it as uncovered and fails.

## Doc comments (PHPDoc)

Enforced by PHP_CodeSniffer (`phpcs.xml.dist`: PEAR class and function comment sniffs, Generic doc
comment, Slevomat doc comment spacing) and PHPStan.

- **Every class, interface, trait and enum, and every public method, has a doc comment** whose
  first line is one summary sentence saying what it does for the caller. Properties and constants
  that carry a doc comment start with a summary too.
- `@param` and `@return` only when they add something the type does not say (units, ranges, what
  `null` means, array shapes and generics Larastan needs). When present, `@param` has a description.
- `@throws` for every exception a caller must handle, with "when ...": `@throws SourceMissing when
  the index does not exist.`
- Tag groups, separated by one blank line, in this order: generics (`@template`, `@extends`,
  `@implements`, `@use`), then `@var` / `@param` / `@return`, then `@throws`, then `@see` /
  `@example`. One blank line after the summary.
- Comments explain **why**, not what. No commented-out code (`Squiz.PHP.CommentedOutCode`).
- **`TODO` and `FIXME` name their issue**: `TODO(#12): ...` (the local sniff
  `Bway.Commenting.TodoWithIssue`, in `phpcs/Bway/`). Deferred work without an issue is invisible to
  planning; larger gaps go to [tech-debt.md](tech-debt.md) with their number.

## Readability rules

Enforced by the tools named in each line, at error level:

- Cognitive complexity at most **15** per method (`tomasvotruba/cognitive-complexity` in PHPStan,
  `sonarjs/cognitive-complexity` in ESLint). Cyclomatic complexity at most 10 (PHPMD).
- Nesting at most **3** (`Generic.Metrics.NestingLevel`, ESLint `max-depth`).
- At most **4** parameters (PHPMD `ExcessiveParameterList`, ESLint `max-params`); beyond that, take
  a DTO.
- One statement per line (`Generic.Formatting.DisallowMultipleStatements`). A condition with `&&` /
  `||` puts each operand on its own line with the operator leading
  (`SlevomatCodingStandard.ControlStructures.RequireMultiLineCondition`). Blank lines around
  multi-line blocks and before `return`, `throw`, `continue` and `break` (Slevomat block and jump
  spacing). Early exit instead of `else` (`EarlyExit`, PHPMD `ElseExpression`).
- No magic numbers in `app/` and `routes/` (`phpmnd`): name them as constants
  (`PREVIEW_LENGTH = 96`). Tests, migrations and config are exempt: there the literal is the
  readable form.
- No unused code: imports, variables, parameters, private members (PHPMD, Slevomat), public methods,
  constants and properties nothing calls (`shipmonk/dead-code-detector`), unused packages
  (`composer-unused`, `knip`). Remove it; do not keep it "for later".
- Strict types in every file (`declare(strict_types=1)`, Pint and Slevomat), strict comparisons
  (`===`), native type declarations wherever PHP has them.

## Naming

- Intention-revealing names in the domain vocabulary of [glossary.md](glossary.md): `ShoppingList`,
  `Sector`, `Partition`, `Route`, `VisitPlanner` — never the Portuguese names of v1 (`Setor`,
  `Divisoria`, `Carrinho`).
- No abbreviations except those in the glossary.
- Booleans read as predicates: `isEntrance`, `isWalkable`, `hasRoute`.
- Exceptions are named for what went wrong, without an `Exception` suffix: `SourceMissing`,
  `DuplicateListItem`.

## Language

Everything is in English: identifiers, comments, log messages, exception messages, documentation,
commit messages, PR and issue text. The only Portuguese in the repository is the 2020 paper and the
v1 names quoted in the glossary.

## Branches, commits and pull requests

- `master` is the default branch and is never pushed to directly. Branch prefixes: `feat/`, `fix/`,
  `docs/`, `refactor/`, `chore/`, `test/`, `ci/`.
- Commits follow **Conventional Commits** (`type(scope): description`, lower-case description),
  checked by commitlint in the `commit-msg` hook and, for every commit of a pull request, in CI.
- **The commit type is the version.** `feat` → minor; `fix`, `perf`, `refactor` → patch; a `!` after
  the type or a `BREAKING CHANGE:` footer → major; `docs`, `test`, `build`, `ci`, `chore`, `style` →
  no release.
- Small pull requests, one concern each. Pull requests are squash-merged; the PR title becomes the
  commit, so it follows Conventional Commits too. The PR template asks for the what and why, the
  issue, the verification **as results** ("18/18 tests, coverage 100%"), and the risks.
- Link the issue with an English closing keyword (`Closes #12`, `Fixes #12`); anything else does not
  link, silently.

## Context base

`.context/docs/` is the source. `AGENTS.md`, `.claude/CLAUDE.md` and
`.github/copilot-instructions.md` are copies of `.context/docs/README.md` written by
`php artisan context:export`; edit the source and re-export. Never generate the surfaces with the
dotcontext `exportDocs`, `exportContext` or `reverseSync` tools: they write targets this repository
does not control and re-import generated files as sources.
