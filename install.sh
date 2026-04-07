#!/bin/bash
# ╔══════════════════════════════════════════════════════════════╗
# ║          unraid-cpufreq-inject – Installer                  ║
# ║          https://github.com/Qauttrolab/unraid-cpufreq-inject║
# ╚══════════════════════════════════════════════════════════════╝

set -e

PLUGIN_NAME="cpufreq-inject"
PLUGIN_DIR="/boot/config/plugins/${PLUGIN_NAME}"
EMHTTP_LINK="/usr/local/emhttp/plugins/${PLUGIN_NAME}"
GO_FILE="/boot/config/go"
GO_ENTRY="ln -sf ${PLUGIN_DIR} ${EMHTTP_LINK}"
REPO_URL="https://raw.githubusercontent.com/Qauttrolab/unraid-cpufreq-inject/main"

echo "╔══════════════════════════════════════════════════╗"
echo "║        unraid-cpufreq-inject Installer          ║"
echo "╚══════════════════════════════════════════════════╝"
echo ""

# ── 1. Plugin-Verzeichnis anlegen ─────────────────────────────
mkdir -p "$PLUGIN_DIR"
echo "[1/5] Plugin-Verzeichnis: $PLUGIN_DIR"

# ── 2. Files herunterladen ────────────────────────────────────
echo "[2/5] Lade Files herunter..."
curl -fsSL "${REPO_URL}/cpufreq-data.php" -o "${PLUGIN_DIR}/cpufreq-data.php"
curl -fsSL "${REPO_URL}/inject.page"      -o "${PLUGIN_DIR}/inject.page"
echo "      cpufreq-data.php  ✓"
echo "      inject.page       ✓"

# ── 3. Symlink aktivieren ─────────────────────────────────────
ln -sf "$PLUGIN_DIR" "$EMHTTP_LINK"
echo "[3/5] Symlink: $EMHTTP_LINK -> $PLUGIN_DIR"

# ── 4. /boot/config/go bereinigen ────────────────────────────
echo "[4/5] /boot/config/go aktualisieren..."
# Alte Einträge (ggf. vorherige Installationen) entfernen
sed -i '/cpufreq-inject/Id' "$GO_FILE"
# Sauberen Eintrag anhängen
echo "" >> "$GO_FILE"
echo "# cpufreq-inject – live CPU frequency on Unraid dashboard" >> "$GO_FILE"
echo "$GO_ENTRY" >> "$GO_FILE"
echo "      Eintrag gesetzt ✓"

# ── 5. Fertig ─────────────────────────────────────────────────
echo "[5/5] Abgeschlossen"
echo ""
echo "✓ unraid-cpufreq-inject installiert"
echo "  → Browser Hard-Refresh (Ctrl+Shift+R) auf dem Unraid Dashboard"
echo ""
echo "Aktueller /boot/config/go:"
cat -n "$GO_FILE"
