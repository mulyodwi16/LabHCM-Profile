#!/usr/bin/env bash
# Daily backup: MySQL dump + public storage archive, 7-day retention.
#
# Crontab (run as hcm, daily 02:00):
#   0 2 * * * /home/hcm/LabHCM-Profile/backup.sh >> /var/log/hcm-backup.log 2>&1
#
# Usage: ./backup.sh [--dry-run]
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT"

BACKUP_ROOT="/opt/backup/hcm"
RETENTION_DAYS=7
MYSQL_CONTAINER="hcm_mysql"
STAMP="$(date +%Y%m%d_%H%M%S)"

DRY_RUN=false
if [[ "${1:-}" == "--dry-run" ]]; then
  DRY_RUN=true
elif [[ -n "${1:-}" ]]; then
  echo "Usage: $0 [--dry-run]" >&2
  exit 1
fi

if [[ ! -f .env ]]; then
  echo "Abort: root .env not found (need DB_PASSWORD)." >&2
  exit 1
fi

# shellcheck disable=SC1091
set -a && source .env && set +a

if [[ -z "${DB_PASSWORD:-}" ]]; then
  echo "Abort: DB_PASSWORD not set in .env" >&2
  exit 1
fi

docker_exec() {
  if docker info &>/dev/null 2>&1; then
    docker exec "$@"
  else
    sg docker -c "docker exec $(printf '%q ' "$@")"
  fi
}

SQL_OUT="${BACKUP_ROOT}/hcm_lab_${STAMP}.sql.gz"
TAR_OUT="${BACKUP_ROOT}/storage_public_${STAMP}.tar.gz"
STORAGE_SRC="${ROOT}/src/storage/app/public"

echo "==> Backup stamp: $STAMP"

if $DRY_RUN; then
  echo "[dry-run] mkdir -p ${BACKUP_ROOT}"
  echo "[dry-run] docker exec ${MYSQL_CONTAINER} mysqldump -u hcm -p*** hcm_lab | gzip > ${SQL_OUT}"
  echo "[dry-run] tar -czf ${TAR_OUT} -C ${ROOT}/src/storage/app public"
  echo "[dry-run] find ${BACKUP_ROOT} -type f \\( -name '*.sql.gz' -o -name '*.tar.gz' \\) -mtime +${RETENTION_DAYS} -delete"
  echo "[dry-run] backup complete (no files written)"
  exit 0
fi

if [[ ! -d "$BACKUP_ROOT" ]]; then
  sudo mkdir -p "$BACKUP_ROOT"
  sudo chown "$(whoami):$(whoami)" "$BACKUP_ROOT"
fi

echo "==> MySQL dump"
docker_exec "$MYSQL_CONTAINER" mysqldump \
  -u hcm \
  -p"$DB_PASSWORD" \
  --single-transaction \
  --routines \
  --triggers \
  hcm_lab | gzip > "$SQL_OUT"

echo "==> Storage archive"
if [[ -d "$STORAGE_SRC" ]]; then
  tar -czf "$TAR_OUT" -C "${ROOT}/src/storage/app" public
else
  echo "Warning: ${STORAGE_SRC} not found, skipping storage tar." >&2
fi

echo "==> Retention (>${RETENTION_DAYS} days)"
find "$BACKUP_ROOT" -type f \( -name '*.sql.gz' -o -name '*.tar.gz' \) -mtime +"$RETENTION_DAYS" -delete

echo "==> Backup files"
ls -lh "$SQL_OUT" ${TAR_OUT:+"$TAR_OUT"}
echo "==> Backup complete"
