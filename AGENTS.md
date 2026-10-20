# AGENTS.md

## What this repo is

- Static visual prototype of a personal-finance tracker (school project, V1): plain PHP used **only** to route pages and include shared components (`header.php`, `navbar.php`, `footer.php`), plus HTML/CSS/JS. **No database, no backend logic, no cookies, no sessions.**
- Entry point: `index.php`. The project root (`http://localhost/TrackerFinanzasPersonales/`) redirects to `login.php`; the access check (`admin` / `admin123`) is done in JavaScript and, when it succeeds, it goes to `index.php?page=dashboard`. Everything else is behind `index.php?page=<slug>` (whitelist in `modulos()` in `includes/funciones.php`).
- `database/db.sql` is a conceptual reference for the future MER only — nothing reads it yet.
- `actions/` files are reserved for the future logic of each module; the forms point to them by contract but, in V1, their buttons do not process anything.

## Running locally

- XAMPP: place project in `htdocs/`, start Apache, open `http://localhost/TrackerFinanzasPersonales/` (the login appears automatically).
- No build step, no package manager, no env vars.

## Architecture

- `index.php` is the front controller: `index.php?page=movimientos` loads `pages/movimientos.html` between the shared header/footer. Never link directly into `pages/`.
- `pages/` holds **pure HTML fragments** (no PHP, no `<html>`/`<head>`/`<body>`): they are included inside the shared template.
- Shared layout in `includes/`; `modulos()` is the single source of the valid module list and the only place that maps a slug to a file.
- The layout is a top bar plus a left sidebar; on small screens the sidebar collapses into a button handled by `assets/js/app.js`.

### Required pattern for any new page

A page in `pages/` is a content-only fragment. To add a module:

1. Create `pages/<slug>.html` with the module content.
2. Register it in `modulos()` (`includes/funciones.php`): slug, title, file name and icon.

`index.php` derives `$titulo_pagina` and `$pagina_activa` from that registry, so the navbar highlights the active item automatically.

## Conventions

- UI strings and identifiers are Spanish (`movimiento`, `presupuesto`, `meta_ahorro`).
- The screens do not show invented financial data: the modules present their forms, tables and charts empty until the logic of each module is implemented.
- `docs/Roles.md` assigns per-file ownership — coordinate before editing another persona's files.
- V1 scope: access check with fixed credentials (no real auth), no AI/gamification features.
