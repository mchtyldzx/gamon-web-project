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

## Technologies

### Frontend
- HTML5
- CSS3
- JavaScript (Vanilla JS)

### Backend
- PHP

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
