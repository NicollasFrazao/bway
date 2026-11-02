---
type: doc
name: development-workflow
description: Docker setup, day-to-day make targets and recipes
category: workflow
generated: 2026-11-01
status: filled
scaffoldVersion: "2.0.0"
---

# Development workflow

The rules are in [development-conventions.md](development-conventions.md); this file is the how-to.

## Requirements

- Docker with Compose v2. PHP, Composer and MySQL run in containers; the host does not need them.
- Node.js 22 on the host, for the git hooks (husky, lint-staged, commitlint), ESLint, knip and Vite.

## First run

```bash
git clone https://github.com/NicollasFrazao/bway.git && cd bway
make up          # builds the image, starts app + MySQL, waits until http://localhost:8000 answers
make install     # Composer and npm dependencies, and the git hooks
```

`make up` alone is enough to see the welcome page: on a fresh clone the app container's entrypoint
(`docker/app/entrypoint.sh`) runs `composer install`, copies `.env.example` to `.env`, generates the
app key and runs the migrations before starting the server.

Ports: `APP_PORT` (default 8000) for the app and `FORWARD_DB_PORT` (default 33060) for MySQL
(user `bway`, password `bway`, database `bway`). Set them in the shell to change them. The image runs
as the host user (`UID`/`GID`, default 1000), so files it writes in the bind mount stay yours.

## Day to day

| Command | What it does |
| --- | --- |
| `make up` / `make down` | Start / stop the stack (the database volume is kept; `docker compose down -v` drops it) |
| `make sh` | Shell in the app container (`php artisan ...`, `composer ...`) |
| `make test` | Pest |
| `make coverage` | Pest with the 80% coverage gate |
| `make lint` | Pint (check), PHP_CodeSniffer, ESLint |
| `make fix` | Apply Pint, PHP_CodeSniffer and Rector fixes |
| `make analyse` | PHPStan, PHPMD, magic numbers, Rector dry run |
| `make architecture` | Deptrac |
| `make unused` | composer-unused and knip |
| `make audit` | `composer audit` and `npm audit` |
| `make context` | `php artisan context:check` |
| `make gate` | All of the above, as CI runs them |

`make help` lists the targets. Each target is a thin wrapper over a Composer or npm script, listed
in [tooling.md](tooling.md).

## Recipes

### Change the context base

1. Edit the file under `.context/docs/` (the index is `.context/docs/README.md`).
2. If the index changed: `docker compose exec app php artisan context:export`.
3. Commit the source and the three surfaces together; `context:check` runs in the hook and in CI.

### Add a Composer package

1. `docker compose exec app composer require vendor/package`.
2. If `app/` uses it, add its namespace to the `Vendor` layer in `deptrac.yaml`.
3. If nothing imports it by name (a framework plugin), say why in a `composer-unused.php` filter.

### Add a domain class (from B1 on)

1. Put it in `app/Domain/<Context>/` ([architecture.md](architecture.md), "Where things go").
2. Write its unit test in `tests/Unit/Domain/<Context>/` first; unit tests never boot Laravel.
3. `make gate`.
