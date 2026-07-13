#!/usr/bin/env bash
# Production deploy: pull code, build deps/assets, migrate, reload app+nginx.
#
# Rollback: if deploy fails mid-run, check out the previous known-good commit
#   git log --oneline -5
#   git checkout <prev-sha>
#   bash deploy.sh
# If migrations ran but app is broken, restore DB from latest backup:
#   ls -lt /opt/backup/hcm/*.sql.gz
#   zcat /opt/backup/hcm/<file>.sql.gz | docker exec -i hcm_mysql mysql -u hcm -p"$DB_PASSWORD" hcm_lab
#
# Usage: ./deploy.sh [--dry-run]
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT"

DRY_RUN=false
if [[ "${1:-}" == "--dry-run" ]]; then
  DRY_RUN=true
elif [[ -n "${1:-}" ]]; then
  echo "Usage: $0 [--dry-run]" >&2
  exit 1
fi

run() {
  if $DRY_RUN; then
    printf '[dry-run]'; printf ' %q' "$@"; printf '\n'
  else
    "$@"
  fi
}

dc() {
  if docker info &>/dev/null 2>&1; then
    docker compose "$@"
  else
    sg docker -c "cd $(printf '%q' "$ROOT") && docker compose $(printf '%q ' "$@")"
  fi
}

dc_run() {
  if $DRY_RUN; then
    printf '[dry-run] docker compose run --rm %s\n' "$*"
  elif docker info &>/dev/null 2>&1; then
    docker compose run --rm "$@"
  else
    sg docker -c "cd $(printf '%q' "$ROOT") && docker compose run --rm $(printf '%q ' "$@")"
  fi
}

if [[ -n "$(git status --porcelain)" ]]; then
  echo "Abort: uncommitted changes detected. Commit or stash before deploy." >&2
  git status --short >&2
  exit 1
fi

echo "==> Deploy from $ROOT"
run git pull --ff-only

echo "==> Composer (production)"
dc_run app composer install --no-interaction --optimize-autoloader --no-dev

echo "==> Database migrations"
dc_run app php artisan migrate --force

echo "==> Frontend build"
run rm -f src/public/hot
dc_run node sh -c "npm ci || npm install; npm run build"

echo "==> Fix storage permissions"
dc_run app sh -c 'chown -R www-data:www-data storage bootstrap/cache && chmod -R ug+w storage bootstrap/cache && find storage/app/public -type d -exec chmod 775 {} \;'

echo "==> Laravel optimize"
dc_run app php artisan optimize

echo "==> Reload app + nginx (no node/mysql)"
if $DRY_RUN; then
  printf '[dry-run] docker compose up -d --no-deps app nginx\n'
else
  dc up -d --no-deps app nginx
fi

echo "==> Deploy complete"
