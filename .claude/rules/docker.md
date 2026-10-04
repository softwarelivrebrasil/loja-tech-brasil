# Docker: dev environment

`docker-compose.yml` brings up four services: `postgres`, `redis`, `backend` (`apps/backend`), `admin` (`apps/admin`). It's a **dev** setup, optimized for small images and for working out of the box on a machine with nothing but Docker installed — no host PHP/Composer/Node required. There's no production Dockerfile yet (see root `CLAUDE.md` roadmap).

```bash
cp .env.example .env            # docker-compose's own vars — POSTGRES_*, ports
docker compose up                 # apps/*/.env get created from .env.example automatically on first boot
```

Backend: http://localhost:8000 · Admin: http://localhost:8001 (`/admin`, `/app`, `/`, per `filament-panels.md`).

## Why the two apps' images differ

- **`docker/backend/Dockerfile`** is [`dunglas/frankenphp`](https://frankenphp.dev) (Alpine base) running `php artisan octane:start --watch` — matches README's committed stack (`apps/backend` is Laravel Octane, see `README.md`'s stack table). `--watch` needs Node, which the image already carries for asset builds anyway, so it costs nothing extra. Running Octane in dev too (not just prod) is deliberate: README's own caution about Octane is "leaked state between requests is a bug class of its own — worth mapping early in tests," which only happens if dev actually runs the same long-running-process model as prod.
- **`docker/admin/Dockerfile`** is plain `php:8.4-cli-alpine` running PHP's built-in server directly, with Laravel's own router script (`php -S 0.0.0.0:8001 -t public vendor/laravel/framework/.../server.php`) — **not** `php artisan serve`. `artisan serve`'s `ServeCommand` spawns that same built-in server as a subprocess with a curated environment that silently drops Docker-injected env vars: confirmed empirically (`getenv('DB_CONNECTION')` was empty inside the request-serving process while correct for any `artisan`/`tinker` command), which showed up as `apps/admin` connecting to a nonexistent SQLite file instead of the `postgres` service despite `DB_CONNECTION=pgsql` being set correctly in the container's environment. Driving the built-in server directly with the same router script Laravel uses internally gets identical routing behavior without losing the environment. `apps/admin` is intentionally **not** Octane either way (`project-shape.md`: separate deploy cadence, separate operational profile from the API) — a Filament admin panel with low internal traffic doesn't need a long-running-process model.

Neither image bakes in application code, `vendor/`, or `node_modules/` — `docker-compose.yml` bind-mounts the whole repo at `/var/www` in both containers so `packages/*` (reached via each app's Composer path repository, `../../packages/*`) resolves exactly like it does on the host. `vendor/`, `node_modules/`, and `public/build` each get their own named volume mounted over that same bind mount (Docker's standard "shadow a subpath of a bind mount with a named volume" pattern) — necessary for `node_modules` specifically, since Vite/esbuild ship platform-native binaries that a macOS-installed `node_modules` can't provide inside a Linux container; applied consistently to `vendor/` and `public/build` too so the whole dev setup is self-contained and never depends on anything having been installed on the host first.

`docker/entrypoint.sh` is shared by both containers (paths inside it are relative to `WORKDIR`, which each Dockerfile sets to that app's own root) — on every boot it lazily runs `composer install`/`npm ci`/`npm run build` if those named volumes are empty and waits for Postgres to accept connections before handing off to the app's actual process. It only runs `php artisan migrate --force` when `RUN_MIGRATIONS=true`, which `docker-compose.yml` sets on `backend` only — see `database.md` for why `apps/admin` never migrates.

## Postgres and Redis

One `postgres` service, one database, shared by both apps — see `database.md` for the migration-ownership rules that follow from that. `redis` is wired (`REDIS_HOST`/`REDIS_PORT` env vars set in both app containers) but nothing consumes it yet — `CACHE_STORE`/`QUEUE_CONNECTION`/`SESSION_DRIVER` in `apps/*/.env.example` are still Laravel's defaults (`database`, `database`, `database`). Switching those to Redis, and installing Horizon (README's committed queue stack), is separate follow-up work, not done as part of this Docker setup.
