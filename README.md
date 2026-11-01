# bway

Shopping-list web app that turns a user's list into a supermarket visit ordered by sector, using routes computed with A* over a mapped floor plan.

Context: Final project (TCC) of the Computer Engineering degree at Universidade Santa Cecília, 2020.

## Team

Developed by Lucas Henrique dos Santos, Marcus Vinicius Alves Rodrigues dos Santos, Nícollas Frazão and Pedro Marcos Gonçalves de Araujo Diegues Pinto. The code in this repository was written by Nícollas Frazão; the paper (`.docx` at the repository root) is the team's.

## What it does

- **Accounts.** Sign-up with name, e-mail and password; login keeps the user in the Laravel session; logout.
- **Shopping list.** Search the product catalogue by name (products already on the list are hidden), add and remove products, clear the whole list.
- **Cart for a store.** For a given establishment, the app keeps the products of the list that the store carries, groups them by sector and orders the sectors for the visit: starting at the entrance sector (or a given origin), it repeatedly picks the sector with the shortest precomputed route from the current one (a greedy nearest-neighbour pass over A* route lengths). Marking a product as acquired removes it from the list and therefore from the cart.
- **Routes.** `GET /estabelecimentos/{id}/rotas/{origin}/{destination}` returns the precomputed path between two sectors, read from `storage/app/public/estabelecimentos/rotas/<store>-<origin>-<destination>.json`.
- **Map screen.** Shows a static image of the test floor plan.
- **Establishments.** JSON listing and detail (with sectors and partitions) and an admin page that lists them; creating an establishment from the UI is not implemented.

### Route generation (Python)

`public/dist/py/app.py` is a small Flask service, separate from the Laravel app:

- `/mapearPlanta` reads a floor-plan image with Pillow, detects the outer walls, sectors and partitions (shelves) by colour and writes a grid (`0` free floor, `1` wall, `2` sector, `3` partition, `4` route) to `storage/app/public/estabelecimentos/mapeamentos/mapeamento.json`.
- `/gerarRota/<store>/<origin>/<destination>` runs an A* search (Manhattan-distance cost) between the centres of two sectors on that grid, saves the path as JSON under `rotas/` and records it in the `tb_rota` table, so later requests reuse the stored route.

The repository includes a mapping and the precomputed routes for establishment 1 (sectors 2, 3, 5, 6, 10 and 11), plus the test floor plan in AutoCAD, Photoshop and JPEG formats.

## Tech stack

- PHP 7.2.5+, Laravel 7 (locked at 7.25.0), fruitcake/laravel-cors 2.0, Guzzle 6.5
- MySQL, schema in Laravel migrations
- Blade views with AngularJS 1.8 controllers inline in each view, Bootstrap 4.5, jQuery 3.5
- Python 3 with Flask, Pillow and mysql-connector for floor-plan mapping and routing

## Running locally

```bash
composer install
cp .env.example .env          # set DB_DATABASE, DB_USERNAME, DB_PASSWORD
php artisan key:generate
php artisan migrate
php artisan db:seed           # seeds the product catalogue only
php artisan serve
```

What is not provided:

- **Store data.** There is no seeder for `tb_estabelecimento`, `tb_setor` (with coordinates and the `ic_entrada` entrance flag), `tb_divisoria` or `setor_produto`. Without them the list works, but the cart is empty. The bundled route files assume establishment 1 with the sector ids above.
- **Store selection.** The current establishment is hard-coded to `1` in the views.
- **Python service settings.** The MySQL connection in `app.py` is hard-coded for a local `db_bway` database, and the script clears the console with `cls` (Windows). `mapearPlanta` writes `mapeamento.json`, while `gerarRota` reads `<store>.json`, so the file has to be renamed between the two steps. To run it: `pip install flask pillow mysql-connector-python` and `python public/dist/py/app.py`.

## Project structure

```
app/                         Eloquent models (Usuario, Produto, Estabelecimento, Setor, Divisoria, Carrinho)
app/Http/Controllers/        Login, Usuario, Produto, ListaCompras, Carrinho, Estabelecimento, Rota
routes/web.php               resource routes plus session-guarded page routes
resources/views/             login, sign-up, shopping list, cart, map, admin establishments
database/migrations/         schema (users, products, establishments, sectors, partitions, list, cart, routes)
public/dist/py/app.py        floor-plan mapping and A* route generation
storage/app/public/estabelecimentos/   floor plans, mapping and precomputed routes
```

## Branches

`master` is the default branch. `dev` was merged into it in January 2021 and has no changes beyond `master`.

## Notes

- The paper submitted with the project (the `.docx` at the repository root) describes a React Native client and BLE beacons placed in each sector to tell the app where the user is. Neither is in this repository; the `ds_beacon` column in `tb_setor` is the only trace of it. This repository holds the PHP back end, its web screens and the Python routing.
- Passwords are stored and compared in plain text, and the routes take the user id from the URL without checking it against the session.
- The cart's ordering compares route lengths in grid steps, not distances; a pair of sectors without a stored route counts as length 0.
- `RotaController` builds both a Windows and a Linux path for the route files, a leftover of developing on Windows.
- There are no tests beyond the Laravel examples.
