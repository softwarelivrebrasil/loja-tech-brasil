#!/bin/sh
set -e

# Shared by apps/backend and apps/admin's containers — every path below is
# relative to WORKDIR, which each Dockerfile sets to that app's own root
# (/var/www/apps/backend or /var/www/apps/admin), so this one script works
# for both.

if [ ! -f .env ]; then
  echo "==> .env missing, copying from .env.example"
  cp .env.example .env
fi

if [ ! -f vendor/autoload.php ]; then
  echo "==> vendor/ missing, running composer install"
  composer install --no-interaction --prefer-dist
fi

if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
  echo "==> APP_KEY missing, generating"
  php artisan key:generate --force
fi

if [ ! -x node_modules/.bin/vite ]; then
  if [ -f package-lock.json ]; then
    echo "==> node_modules/ missing, running npm ci"
    npm ci
  else
    echo "==> node_modules/ missing and no package-lock.json, running npm install"
    npm install
  fi
fi

if [ ! -d public/build ] || [ -z "$(ls -A public/build 2>/dev/null)" ]; then
  echo "==> public/build missing, running npm run build"
  npm run build
fi

echo "==> waiting for postgres at ${DB_HOST:-postgres}:${DB_PORT:-5432}"
until nc -z "${DB_HOST:-postgres}" "${DB_PORT:-5432}"; do
  sleep 1
done

# Only apps/backend runs migrations (its own + every packages/* migration,
# auto-loaded via each module's ServiceProvider) — apps/admin only ever reads
# and writes through those same tables via packages/*, never migrates them
# itself. See .claude/rules/database.md. Set via RUN_MIGRATIONS in
# docker-compose.yml, backend only.
if [ "${RUN_MIGRATIONS}" = "true" ]; then
  echo "==> php artisan migrate --force"
  php artisan migrate --force
fi

exec "$@"
