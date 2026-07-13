#!/usr/bin/env bash
# Smoke-test production endpoints and config. Usage: ./scripts/verify-features.sh
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

dc() {
  if docker info &>/dev/null 2>&1; then docker compose "$@"
  else sg docker -c "cd $(printf '%q' "$ROOT") && docker compose $(printf '%q ' "$@")"
  fi
}

probe() {
  local path="$1"
  local expect="${2:-200}"
  local code
  code=$(dc exec -T nginx wget -q -S -O /dev/null "http://127.0.0.1${path}" 2>&1 | awk '/HTTP\//{print $2; exit}')
  if [[ "$code" == "$expect" ]]; then
    echo "OK  $path -> $code"
  else
    echo "FAIL $path -> ${code:-?} (expected $expect)"
    return 1
  fi
}

echo "=== Config ==="
grep -E '^APP_URL=|^SESSION_SECURE_COOKIE=' src/.env || true
dc exec -T app php artisan tinker --execute="echo 'storage_url='.\\Illuminate\\Support\\Facades\\Storage::url('avatars/test.jpg');" 2>/dev/null | tail -1

echo ""
echo "=== HTTP routes ==="
probe /
probe /people
probe /login
probe /admin 302
probe /up

echo ""
echo "=== Database counts ==="
dc exec -T app php artisan tinker --execute="
echo 'users='.\\App\\Models\\User::count();
echo ' dosen='.\\App\\Models\\User::role('dosen')->count();
echo ' members='.\\App\\Models\\User::role('member')->count();
echo ' alumni='.\\App\\Models\\User::role('alumni')->count();
echo ' projects='.\\App\\Models\\Project::count();
echo ' published='.\\App\\Models\\Project::where('published',true)->count();
echo ' gallery='.\\App\\Models\\GalleryItem::count();
" 2>/dev/null | tail -1

echo ""
echo "=== Assets ==="
if [[ -f src/public/build/manifest.json ]]; then echo "OK  build/manifest.json exists"; else echo "FAIL build/manifest.json missing"; fi
if [[ -f src/public/hot ]]; then echo "WARN src/public/hot exists (vite dev)"; else echo "OK  no public/hot"; fi
if [[ -L src/public/storage ]]; then echo "OK  public/storage symlink"; else echo "FAIL public/storage symlink missing"; fi

echo ""
echo "=== Containers ==="
dc ps
