#!/usr/bin/env bash
# Harden L3/L4: UFW (SSH) + DOCKER-USER (restrict published port 8080 to NPM only).
# Usage: sudo bash scripts/harden-firewall.sh <NPM_IP>
set -euo pipefail

if [[ "${EUID:-$(id -u)}" -ne 0 ]]; then
  exec sudo bash "$0" "$@"
fi

NPM_IP="${1:-}"
if [[ -z "$NPM_IP" ]]; then
  echo "Usage: sudo bash scripts/harden-firewall.sh <NPM_IP>" >&2
  exit 1
fi

if ! [[ "$NPM_IP" =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
  echo "Error: NPM_IP must be an IPv4 address, got: $NPM_IP" >&2
  exit 1
fi

export DEBIAN_FRONTEND=noninteractive

# SSH via UFW (does not affect Docker-published ports by itself)
ufw allow 22/tcp
ufw --force enable

# Docker bypasses UFW for published ports — use DOCKER-USER chain
iptables -D DOCKER-USER -p tcp --dport 8080 -s "$NPM_IP" -j ACCEPT 2>/dev/null || true
iptables -D DOCKER-USER -p tcp --dport 8080 -j DROP 2>/dev/null || true
iptables -I DOCKER-USER -p tcp --dport 8080 -s "$NPM_IP" -j ACCEPT
iptables -I DOCKER-USER -p tcp --dport 8080 -j DROP

apt-get install -y iptables-persistent
netfilter-persistent save

echo ""
echo "=== DOCKER-USER rules (8080) ==="
iptables -S DOCKER-USER | grep -E '8080|DOCKER-USER' || iptables -S DOCKER-USER
echo ""
echo "=== UFW ==="
ufw status verbose
echo ""
echo "NPM ($NPM_IP) may reach :8080; all other sources to :8080 are DROPped in DOCKER-USER."
