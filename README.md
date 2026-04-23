<div align="center">

<img src=".github/logo.png" alt="Yammbo" width="320">

# Yammbo Tv

Streaming catalog, metadata and player for the Yammbo TV
companion — web, mobile and TV app in one.

</div>

---

## Overview

Yammbo Tv is the backend and web player for the `tv.yammbo.com` service.
It unifies three pieces behind a single domain:

- a **marketing landing**, blog and auth flows,
- a **Stremio-based** web player for the catalog, meta details, library
  and in-browser playback,
- an **admin panel** for content operations and user management,
- a **mobile / TV APK** companion driven by a dedicated REST API
  (`/api/app-tv/*`), so a phone can log in, manage the library and
  pair with a TV set without typing on the set.

## Features

- **Unified app**: landing, admin, player SPA and APK endpoints on a
  single domain, a single session and a single user database.
- **Yammbo Library**: user-owned "My List" that survives beyond
  third-party catalog availability. Soft-delete, per-episode progress,
  Cinemeta auto-sync for series.
- **Calendar**: upcoming and recent episodes for everything in the
  library, with day-by-day grouping.
- **Premium addon policy**: active subscribers automatically get the
  premium streams addon installed and the free-only YouTube / public-domain
  addons removed; downgrading reverses the change on next login.
- **Pricing and checkout**: three-tier plans with direct Stripe Checkout
  (no trial).
- **i18n with auto-detect**: interface and subtitle languages are seeded
  from the user's locale on first load, with EN / ES / PT / FR support.
- **TV APK pairing** (via `/api/app-tv/*`): JWT-based auth, subscription
  status polling, remote library sync and progress reporting.
- **Service-worker invalidation**: every build carries a hash; clients
  with stale service workers auto-unregister and reload on the next hit.

## Tech stack

| Layer              | Choice                                        |
| ------------------ | --------------------------------------------- |
| Backend            | PHP 8.2, Laravel 12, Wave (SaaS framework)    |
| Admin              | Filament 4                                    |
| Auth               | devdojo/auth + custom JWT for APK             |
| Player front-end   | Stremio Web SPA fork (React)                  |
| Build tool         | Webpack (Stremio Web), Vite (Wave)            |
| Database           | MariaDB 10                                    |
| HTTP               | Apache 2.4 + PHP-FPM                          |
| Billing            | Stripe (checkout + webhook)                   |

## Route surface

| Path                                           | Serves                                                     |
| ---------------------------------------------- | ---------------------------------------------------------- |
| `/`                                            | Marketing landing (redirects to `/app` if signed in)       |
| `/app`, `/app/`                                | Stremio player SPA                                         |
| `/app/{path}`                                  | 301 to `/app/#/{path}` (Stremio uses HashRouter)           |
| `/admin`                                       | Filament admin panel                                       |
| `/auth/login`, `/auth/register`                | devdojo/auth views                                         |
| `/pricing`, `/pricing/checkout`                | Plans and direct Stripe checkout                           |
| `/api/app-tv/whoami`                           | Session identity + subscription status                     |
| `/api/app-tv/login`, `/register`               | JWT auth for the APK                                       |
| `/api/app-tv/library[...]`                     | User library read / toggle / progress / episodes           |
| `/api/app-tv/calendar`                         | Upcoming and recent episodes for the library               |
| `/manifest.json`, `/catalog/*`, `/meta/*`      | Stremio addon (catalog + metadata)                         |
| `/webhook/stripe`                              | Stripe billing webhook                                     |

## Data model (Yammbo tables)

- **`yambo_library`** — `(user_id, meta_id, meta_type)` unique. Stores the
  poster, title, genres, rating and runtime at add-time so the "My List"
  row survives catalog changes upstream. `removed_at` implements soft
  delete; `watched_at` and `watch_progress` track playback state.
- **`yambo_library_episodes`** — `(user_id, meta_id, season, episode)`
  unique. Stores per-episode air date, watched timestamp and watch
  progress. Indexed on `air_date` for the calendar view.

## Addon policy

Applied on every login and on subscription-state changes:

| State         | Installed                 | Uninstalled                                          |
| ------------- | ------------------------- | ---------------------------------------------------- |
| All users     | Cinemeta, Local Files, OpenSubtitles (never removed) | YouTube (`com.linvo.stremiochannels`), Public Domain Movies (`org.stremio.pubdomainmovies`) |
| Premium       | + Premium streams addon   | + WatchHub (`org.stremio.watchhub`)                  |
| Downgrade     | — (premium addon is removed next login) | —                                        |

## Project layout

```
app/
  Http/Controllers/AppTv/          # API for the mobile / TV app
  Http/Controllers/                # Pricing, billing, webhook, auth
  Models/                          # Yambo library + episode models
database/migrations/               # yambo_library, yambo_library_episodes
resources/
  themes/anchor/                   # Wave marketing theme overrides
  views/                           # Pricing, auth, TV-pairing blades
routes/
  web.php                          # Landing, /app, /admin, /pricing
  api.php                          # /api/app-tv/*
public/app/                        # Built Stremio SPA (immutable per-build)
```

The Stremio front-end source lives in a separate working tree under
`stremio-web-src/` on the deploy host and is built independently; the
compiled bundle is copied into `public/app/`.

## Local development

> The app requires Wave's own environment: DB, mailer and Stripe test
> keys. Copy `.env.example` and fill it in before running migrations.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan jwt:secret           # for the APK auth layer
npm ci
npm run dev                       # Vite for Wave theme assets
php artisan serve
```

Run the scheduler (queues + cron) in a separate terminal if needed:

```bash
php artisan schedule:work
php artisan queue:work
```

## Production build

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan optimize
php artisan storage:link
```

## Status

Production. HTTPS with Let's Encrypt. Nightly offsite backups.

## Contact

For bug reports, partnerships or support: **support@yammbo.com**

## License

Proprietary. Not for redistribution. Built on open-source and commercial
upstream components (Wave, Stremio Web, devdojo/auth, Filament); their
respective licenses apply to the upstream portions of the codebase.

© Yammbo. All rights reserved.
