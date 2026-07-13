#!/usr/bin/env bash
# Install Docker Engine + Compose v2 on Ubuntu (run once with sudo password).
set -euo pipefail

if [[ "${EUID:-$(id -u)}" -ne 0 ]]; then
  exec sudo bash "$0" "$@"
fi

export DEBIAN_FRONTEND=noninteractive

apt-get update -qq
apt-get install -y docker.io docker-compose-v2

systemctl enable --now docker

DOCKER_USER="${SUDO_USER:-hcm}"
if id "$DOCKER_USER" &>/dev/null; then
  usermod -aG docker "$DOCKER_USER"
fi

echo ""
echo "=== Docker installed ==="
docker --version
docker compose version
echo ""
echo "IMPORTANT: log out and back in (or run: newgrp docker) so group 'docker' applies to $DOCKER_USER"
