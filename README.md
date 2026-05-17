# GaMon (Garbage Monitoring on Web)

Web application for waste collection, sorting, and recycling information, aimed at citizens, authorized staff, and decision-makers (course project).

## Main Features

- Garbage reporting system
- Waste categorization (household, paper, plastic, glass, etc.)
- Location-based reporting
- Administration dashboard
- Statistical reports and charts
- Data export in CSV and JSON formats
- Responsive Web interface

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

## Database (first run)
- SQLite

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
