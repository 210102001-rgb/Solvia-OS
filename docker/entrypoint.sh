#!/bin/sh
# Solvia.Nova OS — production entrypoint.
# Fail fast on missing secrets; never auto-generate APP_KEY in production
# (an ephemeral key would invalidate sessions/encrypted data on every restart).
set -e

IS_PROD=false
if [ "${APP_ENV:-production}" = "production" ]; then
  IS_PROD=true
fi

# Wait for MySQL when pointed at the compose `db` service.
if [ "${DB_HOST:-}" = "db" ]; then
  echo "Waiting for MySQL at db:${DB_PORT:-3306}..."
  for i in $(seq 1 60); do
    if php -r '$c = @fsockopen(getenv("DB_HOST") ?: "db", (int)(getenv("DB_PORT") ?: 3306)); if ($c) { fclose($c); exit(0); } exit(1);'; then
      break
    fi
    if [ "$i" = "60" ]; then
      echo "ERROR: MySQL not reachable after 120s." >&2
      exit 1
    fi
    sleep 2
  done
fi

mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

if [ "$IS_PROD" = true ]; then
  if [ -z "${APP_KEY:-}" ]; then
    echo "ERROR: APP_KEY is empty. Generate once (php artisan key:generate --show)," >&2
    echo "put it in the server .env as APP_KEY, then restart." >&2
    exit 1
  fi
  if [ "${APP_DEBUG:-false}" = "true" ]; then
    echo "WARNING: APP_DEBUG=true in production. Set APP_DEBUG=false." >&2
  fi
else
  # Dev-only convenience: create .env + key inside throwaway containers.
  if [ ! -f .env ]; then
    echo "No .env found, creating from .env.example (non-production only)"
    cp .env.example .env
  fi
  if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    php artisan key:generate --force || true
  fi
fi

php artisan storage:link 2>/dev/null || true
php artisan package:discover --ansi 2>/dev/null || true

# Explicit opt-in: RUN_MIGRATIONS=true docker compose up ...
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  echo "Running database migrations..."
  php artisan migrate --force
fi

# Cache config/routes/views AFTER env is known (never bake into image —
# cached config freezes env vars at cache time).
if [ "$IS_PROD" = true ]; then
  php artisan optimize
fi

exec "$@"
