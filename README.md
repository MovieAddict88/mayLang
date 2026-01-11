# CineCraze (PHP + MySQL)

This repository now contains a **pure PHP + MySQL** full‑stack version of CineCraze:

- `index.php` – public movie/series site (loads catalog from `/api/playlist.php`)
- `/admin/` – responsive admin dashboard (Dashboard, TMDB Generator, Manual, Bulk, YouTube, Data Management, Users)
- `/auth/` – user registration/login
- `/install/` – web installer (writes `config/config.php` and creates tables)

## Quick install (shared/free hosting)

1. Upload the project to your hosting.
2. Create a MySQL database and user in your hosting control panel.
3. Open: `https://your-domain.com/install/`
4. Enter DB credentials + create the admin account.
5. Import catalog:
   - Admin → **Bulk** → upload your existing `playlist.json`.

Admin login:
- `https://your-domain.com/admin/login.php`

User register/login:
- `https://your-domain.com/auth/register.php`
- `https://your-domain.com/auth/login.php`

> Note: the installer writes `config/config.php`. Keep it private.
