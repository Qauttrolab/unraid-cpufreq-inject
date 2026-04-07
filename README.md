# unraid-cpufreq-inject

Shows the **live CPU frequency (GHz)** next to the processor name on the Unraid dashboard — updated every second, no plugin manager required.

![Screenshot showing "Intel(R) Core(TM) i9-9900T @ 2.10GHz (current: 3.84 GHz)" on the Unraid dashboard](screenshot.png)

---

## How it works

Unraid's dashboard renders the CPU model name statically at boot time (e.g. `@ 2.10GHz`). This project injects a small live counter next to it by:

1. A PHP backend (`cpufreq-data.php`) reads `/proc/cpuinfo` (or `/sys/.../scaling_cur_freq` as fallback) and returns current per-core MHz values as JSON.
2. A JavaScript snippet (`inject.page`) polls that endpoint every second, calculates the average across all cores, and appends a `(current: X.XX GHz)` span next to the CPU section on the dashboard.
3. The injection uses `i.icon-cpu` as the anchor point — no hardcoded CPU model strings, works on any Unraid machine.

The plugin hooks into Unraid's `Menu="Buttons"` mechanism so the script is included in every page load without modifying any Unraid core files.

---

## Requirements

- Unraid 6.x or 7.x
- SSH access to the Unraid server

---

## Installation

### One-liner (recommended)

SSH into your Unraid server and run:

```bash
curl -fsSL https://raw.githubusercontent.com/Qauttrolab/unraid-cpufreq-inject/main/install.sh | bash
```

Then do a hard-refresh in your browser (`Ctrl+Shift+R`) on the Unraid dashboard.

### Manual installation

```bash
# 1. Create plugin directory
mkdir -p /boot/config/plugins/cpufreq-inject

# 2. Download files
curl -fsSL https://raw.githubusercontent.com/Qauttrolab/unraid-cpufreq-inject/main/cpufreq-data.php \
  -o /boot/config/plugins/cpufreq-inject/cpufreq-data.php

curl -fsSL https://raw.githubusercontent.com/Qauttrolab/unraid-cpufreq-inject/main/inject.page \
  -o /boot/config/plugins/cpufreq-inject/inject.page

# 3. Activate symlink (makes plugin available to Unraid webserver)
ln -sf /boot/config/plugins/cpufreq-inject /usr/local/emhttp/plugins/cpufreq-inject

# 4. Make symlink persistent across reboots and Unraid updates
echo "" >> /boot/config/go
echo "# cpufreq-inject – live CPU frequency on Unraid dashboard" >> /boot/config/go
echo "ln -sf /boot/config/plugins/cpufreq-inject /usr/local/emhttp/plugins/cpufreq-inject" >> /boot/config/go
```

Hard-refresh the dashboard (`Ctrl+Shift+R`).

---

## Update

Re-run the installer — it replaces the files and cleans up any old `/boot/config/go` entries automatically:

```bash
curl -fsSL https://raw.githubusercontent.com/Qauttrolab/unraid-cpufreq-inject/main/install.sh | bash
```

---

## Uninstallation

```bash
curl -fsSL https://raw.githubusercontent.com/Qauttrolab/unraid-cpufreq-inject/main/uninstall.sh | bash
```

Or manually:

```bash
rm -f /usr/local/emhttp/plugins/cpufreq-inject
rm -rf /boot/config/plugins/cpufreq-inject
sed -i '/cpufreq-inject/Id' /boot/config/go
```

---

## Update survival

Files are stored on `/boot` (the Unraid USB stick) and therefore survive Unraid updates. The `/boot/config/go` entry re-creates the symlink into `/usr/local/emhttp` on every boot, so the plugin is automatically re-activated after any Unraid update.

---

## Files

| File | Description |
|---|---|
| `cpufreq-data.php` | PHP backend — returns CPU MHz, temperatures, RAM, load, network as JSON |
| `inject.page` | Unraid page definition — injects the JS into every dashboard page load |
| `install.sh` | Installer script |
| `uninstall.sh` | Uninstaller script |

---

## Tested on

- Unraid 7.2.3 — Dell OptiPlex 7070 Micro (Intel Core i9-9900T)

Contributions and test reports for other hardware/Unraid versions welcome.

---

## License

MIT
