# GaMon — Garbage Monitoring on Web

Web application for reporting, tracking and analysing waste accumulation. Built for the Web Technologies course (2026).

**Stack:** PHP 8 · SQLite (PDO) · Vanilla JS (Fetch) · HTML/CSS

## Features

- User registration and login (citizen / staff / decision_maker / admin)
- Waste accumulation report submission and listing
- Report status management (open → assigned → resolved)
- Statistics dashboard with Chart.js charts
- Interactive map with Leaflet.js
- CSV and JSON data export
- External geocoding via Nominatim (OpenStreetMap)
- Admin panel (user list, report overview)

## Setup

```bash
# 1. Initialise the database (from repo root)
php scripts/init-database.php

# 2. Start the built-in server
cd public
php -S localhost:8080
```

Open `http://localhost:8080`

## Structure

```
app/          PHP modules (auth, reports, summary, admin, export)
public/       HTML pages + assets + api/ endpoints
sql/          schema.sql + seed.sql
scripts/      init-database.php
data/         gamon.sqlite (git-ignored)
docs/         scholarly-report.html
```

## License

MIT — see [LICENSE](LICENSE)