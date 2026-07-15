# Hidden Sri Lanka

A community-powered tourism platform for discovering popular and hidden destinations across Sri Lanka. Locals can submit places, which are reviewed by admins before going live.

## Stack

- **Language:** PHP 8.2
- **Database:** PostgreSQL (Replit built-in)
- **Maps:** Leaflet.js (OpenStreetMap, no API key required)
- **CSS/JS:** Vanilla (no build step)

## Running the app

The workflow `Start application` runs:
```
php -S 0.0.0.0:5000 -t .
```

The app serves on port 5000. No build step needed — just start the workflow.

## Default credentials

| Role  | Email                        | Password   |
|-------|------------------------------|------------|
| Admin | admin@hiddensrilanka.lk      | admin123   |

Change the admin password after first login via Admin → Manage Users.

## Database

Replit's built-in PostgreSQL is used. Connection credentials come from environment variables (`PGHOST`, `PGPORT`, `PGDATABASE`, `PGUSER`, `PGPASSWORD`) — set automatically by Replit.

Tables: `users`, `categories`, `places`, `place_images`, `reviews`, `favorites`

## Key directories

- `admin/` — admin-only pages (dashboard, users, places, categories, reviews)
- `user/` — logged-in user pages (add/edit place, profile, dashboard, favorites)
- `includes/` — shared config, session helpers, header/footer/navbar
- `assets/` — CSS, JS, images
- `uploads/` — user-uploaded images (place photos, profile pictures)
- `api/` — simple JSON endpoints used by the frontend JS

## Notes

- `includes/config.php` uses PostgreSQL PDO — adapted from the original MySQL version
- `ORDER BY RANDOM()` used instead of MySQL's `ORDER BY RAND()`
- `$pdo->lastInsertId('table_id_seq')` used for PostgreSQL sequence compatibility
- `BASE_URL` is auto-detected from the HTTP request so it works on Replit's proxy
