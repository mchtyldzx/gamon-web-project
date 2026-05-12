# GaMon (Garbage Monitoring on Web)

Web application for waste collection, sorting, and recycling information, aimed at citizens, authorized staff, and decision-makers (course project).

## Requirements

- [PHP](https://www.php.net/) 8.1+ (built-in server, PDO SQLite)
- SQLite enabled in `php.ini` (`pdo_sqlite`, `sqlite3`)

On Windows without PHP: install from [windows.php.net/download](https://windows.php.net/download) (add `php` to PATH) or use [XAMPP](https://www.apachefriends.org/) / similar.

## Database (first run)

From the repository root:

```bash
php scripts/init-database.php
```

Creates `data/gamon.sqlite` (gitignored) from `sql/schema.sql` and `sql/seed.sql`.

## Run locally

```bash
cd public
php -S localhost:8080
```

Open `http://localhost:8080` — the page loads `api/health.php` and `api/meta.php` via Fetch.

## Front-end vs back-end

- **Front-end (browser):** `public/index.html`, `public/assets/css/`, `public/assets/js/` — HTML, CSS, vanilla JS, Ajax/Fetch to the API.
- **Back-end (server):** `app/` — PHP logic (loaded by scripts under `public/api/`).

The built-in server document root is `public/`, so only that tree is directly reachable; `app/` stays outside the web root.

## Repository layout

- `public/` — static UI + thin PHP entry points in `public/api/`
- `app/` — server-side code (config, PDO, JSON helpers)
- `scripts/` — CLI helpers (`init-database.php`)
- `data/` — SQLite file (ignored by git)
- `sql/` — schema and seed data
- `docs/gamon-architecture.drawio` — architecture diagram (diagrams.net / draw.io)

## License

Dependencies and assets must use open licenses per course rules. Add a `LICENSE` file when the team chooses one.
