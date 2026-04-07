#!/bin/bash
# ╔══════════════════════════════════════════════════════════════╗
# ║       unraid-cpufreq-inject – Uninstaller                   ║
# ╚══════════════════════════════════════════════════════════════╝

set -e

PLUGIN_NAME="cpufreq-inject"
PLUGIN_DIR="/boot/config/plugins/${PLUGIN_NAME}"
EMHTTP_LINK="/usr/local/emhttp/plugins/${PLUGIN_NAME}"
GO_FILE="/boot/config/go"

echo "╔══════════════════════════════════════════════════╗"
echo "║      unraid-cpufreq-inject Uninstaller          ║"
echo "╚══════════════════════════════════════════════════╝"
echo ""

# Symlink entfernen
[ -L "$EMHTTP_LINK" ] && rm "$EMHTTP_LINK" && echo "[1/3] Symlink entfernt" || echo "[1/3] Symlink nicht vorhanden"

# Plugin-Verzeichnis entfernen
[ -d "$PLUGIN_DIR" ] && rm -rf "$PLUGIN_DIR" && echo "[2/3] Plugin-Verzeichnis entfernt" || echo "[2/3] Plugin-Verzeichnis nicht vorhanden"

# /boot/config/go bereinigen
sed -i '/cpufreq-inject/Id' "$GO_FILE"
echo "[3/3] /boot/config/go bereinigt"

echo ""
echo "✓ unraid-cpufreq-inject deinstalliert"
echo "  → Browser Hard-Refresh (Ctrl+Shift+R)"
