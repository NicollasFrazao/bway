---
type: doc
name: tooling
description: Every Composer and npm script, git hook and CI job, and what each one catches
category: tooling
generated: 2026-11-01
status: filled
scaffoldVersion: "2.0.0"
---

# Tooling

## Composer scripts (run in the app container)

| Script | Runs | Config |
| --- | --- | --- |
| `lint` | `lint:pint` + `lint:phpcs` | |
| `lint:pint` | `pint --test` | `pint.json` (Laravel preset + `declare_strict_types`, `strict_comparison`, `strict_param`, `void_return`) |
| `lint:phpcs` | `phpcs` | `phpcs.xml.dist` (PEAR/Generic/Squiz comment sniffs, Slevomat) |
| `analyse` | the four below | |
| `analyse:phpstan` | `phpstan analyse` | `phpstan.neon.dist` (Larastan `max`, strict rules, cognitive complexity 15, dead code) |
| `analyse:phpmd` | `phpmd analyze --ruleset=phpmd.xml app database routes` | `phpmd.xml` (unused code, code size, selected design and clean-code rules) |
| `analyse:magic-numbers` | `phpmnd app routes --extensions=all` | |
| `analyse:rector` | `rector process --dry-run` | `rector.php` (PHP 8.4, dead code, code quality, types, privatization, early return) |
| `check:architecture` | `deptrac analyse --fail-on-uncovered` | `deptrac.yaml` |
| `check:unused` | `composer-unused` | |
| `check:context` | `php artisan context:check` | |
| `test` | `pest` | `phpunit.xml`, `tests/Pest.php` |
| `test:coverage` | `pest --coverage --min=80` (pcov) | |
| `fix` | `pint`, `phpcbf`, `rector process` | |
| `gate` | all of the above plus `composer audit` | |

PHPStan's extensions (Larastan, strict rules, cognitive complexity, dead code detector) are
registered by `phpstan/extension-installer`; Slevomat's sniffs by the PHP_CodeSniffer installer
plugin. Caches go to `storage/framework/cache/` (git-ignored).

## npm scripts (run on the host)

| Script | Runs | Config |
| --- | --- | --- |
| `lint` | `eslint . --max-warnings 0` | `eslint.config.js` (`@eslint/js` recommended, `eslint-plugin-sonarjs` recommended, complexity 15, depth 3, 4 params) |
| `check:unused` | `knip` | `knip.json` |
| `audit` | `npm audit --omit=dev --audit-level=high` | Runtime packages only; Dependabot covers the development ones |
| `build` / `dev` | Vite | `vite.config.js` |
| `prepare` | `husky` (installs the hooks) | |

`.npmrc` sets `ignore-scripts=true`, so `npm ci` runs no install scripts of dependencies;
`make install` runs `npm run prepare` explicitly to install the hooks. The audit includes dev
dependencies on purpose: every npm package here is a dev dependency (Vite builds the assets).

## Artisan commands of this repository

| Command | What it does |
| --- | --- |
| `php artisan context:export` | Writes `AGENTS.md`, `.claude/CLAUDE.md` and `.github/copilot-instructions.md` as byte copies of `.context/docs/README.md` |
| `php artisan context:check` | Fails, naming the surface and the first differing line, when a surface is not a byte copy |

Both live in `app/Console/` (`ContextSurfaces`, `SurfaceDifference`, `SourceMissing` and the two
commands), so the same gates that guard the application guard them.

## Git hooks (husky)

| Hook | Runs |
| --- | --- |
| `pre-commit` | `lint-staged --relative`: Pint and PHP_CodeSniffer on staged PHP files, ESLint on staged JS, `context:check` when the index or a surface is staged |
| `commit-msg` | `commitlint --edit` |

PHP in the hooks goes through `scripts/php`: inside the app container when it is running, otherwise
the host's `php`.

## CI (`.github/workflows/ci.yml`)

One job per check, so each can be a required status check on its own. PHP jobs use
`shivammathur/setup-php` (PHP 8.4, pcov only in `test`) and cache Composer's download directory.

| Job | Command |
| --- | --- |
| `pint` | `composer lint:pint` |
| `phpcs` | `composer lint:phpcs` |
| `phpstan` | `composer analyse:phpstan` |
| `phpmd` | `composer analyse:phpmd` |
| `magic-numbers` | `composer analyse:magic-numbers` |
| `rector` | `composer analyse:rector` |
| `deptrac` | `composer check:architecture` |
| `unused` | `composer check:unused` |
| `test` | `composer test:coverage`; the coverage report (Clover) is uploaded as an artifact |
| `audit` | `composer audit` |
| `context` | `composer check:context` |
| `eslint` | `npm run lint` |
| `knip` | `npm run check:unused` |
| `npm-audit` | `npm run audit` |
| `docker` | `docker compose up -d --wait`, then `curl` the welcome page |
| `commitlint` | every commit of the pull request (pull requests only) |

`.github/workflows/codeql.yml` runs CodeQL (`javascript-typescript` and `actions`) as the `CodeQL`
job. `.github/dependabot.yml` updates Composer, npm and GitHub Actions weekly.
