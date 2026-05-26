# GaMon — Garbage Monitoring on Web

Web application for waste collection, sorting, and recycling management. Course project for **Tehnologii Web 2026**.

## Stack

- **Server:** PHP 8.2 (XAMPP)
- **Database:** SQLite via PDO
- **Front-end:** Vanilla HTML / CSS / JS, Fetch API
- **Charts:** Chart.js (CDN) | **Map:** Leaflet.js (CDN) | **Geocoding:** Nominatim (cURL)

## Roles

| Role | Permissions |
|------|-------------|
| `citizen` | Submit reports, view own reports |
| `staff` | Update report status, log collection events |
| `decision_maker` | View all reports, statistics, charts |
| `admin` | Everything above + user management |

## Setup

```bash
# 1. Create database
php scripts/init-database.php

# 2. Start local server
cd public
php -S localhost:8080
```

Open `http://localhost:8080`

## API Endpoints

| Method | Path | Description |
|--------|------|-------------|
| POST | `/api/auth/register.php` | Register |
| POST | `/api/auth/login.php` | Login |
| GET | `/api/auth/me.php` | Current user + CSRF token |
| GET/POST | `/api/reports.php` | List / create reports |
| PATCH | `/api/reports.php?id=N` | Update status |
| GET | `/api/summary.php` | Stats (period/ranking/trend) |
| GET | `/api/export.php?format=csv\|json\|html\|pdf` | Export |
| POST | `/api/import.php?format=csv\|json` | Import |
| GET | `/api/geocode.php?q=address` | Nominatim proxy |
| GET/POST | `/api/collections.php` | Collection events |
| GET | `/api/admin/users.php` | Users (admin) |
| GET | `/api/admin/stats.php` | System stats (admin) |

## Security

- PDO prepared statements (SQL injection prevention)
- `htmlspecialchars()` / `escHtml()` (XSS prevention)
- `password_hash()` bcrypt
- Session-based auth guard
- CSRF token via `X-CSRF-Token` header

## Project structure

```
app/        server-side PHP modules
public/     HTML pages + API entry points
sql/        schema.sql + seed.sql
scripts/    init-database.php
docs/       scholarly-report.html, architecture diagram
data/       gamon.sqlite (git-ignored)
```

## License

MIT — see [LICENSE](LICENSE)