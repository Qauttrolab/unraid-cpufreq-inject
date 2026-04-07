<?php
header('Content-Type: application/json');
$result = [];

// CPU Frequenz
$mhz = [];
$cpuinfo = @file_get_contents('/proc/cpuinfo');
if ($cpuinfo) {
    preg_match_all('/cpu MHz\s+:\s+([\d.]+)/', $cpuinfo, $matches);
    if (!empty($matches[1])) $mhz = array_map('floatval', $matches[1]);
}
if (empty($mhz)) {
    $i = 0;
    while (file_exists("/sys/devices/system/cpu/cpu{$i}/cpufreq/scaling_cur_freq")) {
        $freq = @file_get_contents("/sys/devices/system/cpu/cpu{$i}/cpufreq/scaling_cur_freq");
        if ($freq !== false) $mhz[] = floatval(trim($freq)) / 1000;
        $i++;
    }
}
$result['cpu_mhz'] = $mhz;

// CPU Zeiten
$cpuTimes = [];
$stat = @file_get_contents('/proc/stat');
if ($stat) {
    preg_match_all('/^cpu(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/m', $stat, $matches, PREG_SET_ORDER);
    foreach ($matches as $m) {
        $user = intval($m[2]); $nice = intval($m[3]); $system = intval($m[4]);
        $idle = intval($m[5]); $iowait = intval($m[6]); $irq = intval($m[7]); $softirq = intval($m[8]);
        $total = $user + $nice + $system + $idle + $iowait + $irq + $softirq;
        $cpuTimes[] = ['idle' => $idle + $iowait, 'total' => $total];
    }
}
$result['cpu_times'] = $cpuTimes;

// Temperatur
$temps = [];
$hwmonDirs = glob('/sys/class/hwmon/hwmon*');
if ($hwmonDirs) {
    foreach ($hwmonDirs as $dir) {
        $chipName = @file_get_contents("{$dir}/name");
        $chipName = $chipName ? trim($chipName) : basename($dir);
        $tempFiles = glob("{$dir}/temp*_input");
        foreach ($tempFiles as $tf) {
            $val = @file_get_contents($tf);
            if ($val === false) continue;
            $val = floatval(trim($val)) / 1000;
            if ($val <= 0 || $val > 150) continue;
            $labelFile = str_replace('_input', '_label', $tf);
            $label = @file_get_contents($labelFile);
            $label = $label ? trim($label) : basename($tf, '_input');
            $temps[] = ['name' => $chipName . ' / ' . $label, 'value' => $val];
        }
    }
}
$result['temps'] = $temps;

// Load
$loadavg = @file_get_contents('/proc/loadavg');
if ($loadavg) {
    $parts = explode(' ', trim($loadavg));
    $result['load'] = ['load1' => floatval($parts[0]), 'load5' => floatval($parts[1]), 'load15' => floatval($parts[2])];
}

// RAM
$meminfo = @file_get_contents('/proc/meminfo');
if ($meminfo) {
    $mem = [];
    preg_match_all('/^(\w+):\s+(\d+)/m', $meminfo, $matches, PREG_SET_ORDER);
    foreach ($matches as $m) $mem[$m[1]] = intval($m[2]) * 1024;
    $total = isset($mem['MemTotal']) ? $mem['MemTotal'] : 0;
    $free = isset($mem['MemFree']) ? $mem['MemFree'] : 0;
    $available = isset($mem['MemAvailable']) ? $mem['MemAvailable'] : 0;
    $cached = isset($mem['Cached']) ? $mem['Cached'] : 0;
    $buffers = isset($mem['Buffers']) ? $mem['Buffers'] : 0;
    $used = $total - $free - $cached - $buffers;
    $result['ram'] = [
        'total' => $total, 'free' => $free, 'available' => $available,
        'used' => $used, 'cached' => $cached, 'buffers' => $buffers,
        'swap_total' => isset($mem['SwapTotal']) ? $mem['SwapTotal'] : 0,
        'swap_free' => isset($mem['SwapFree']) ? $mem['SwapFree'] : 0
    ];
}

// Netzwerk
$network = [];
$netDev = @file_get_contents('/proc/net/dev');
if ($netDev) {
    $lines = explode("\n", trim($netDev));
    foreach ($lines as $line) {
        if (strpos($line, ':') === false) continue;
        $parts = preg_split('/[\s:]+/', trim($line));
        $iface = $parts[0];
        if (in_array($iface, ['lo']) || strpos($iface, 'docker') === 0 ||
            strpos($iface, 'br-') === 0 || strpos($iface, 'veth') === 0 ||
            strpos($iface, 'virbr') === 0 || strpos($iface, 'vnet') === 0 ||
            strpos($iface, 'shim') === 0) continue;
        $network[$iface] = [
            'rx_bytes' => floatval($parts[1]), 'rx_packets' => floatval($parts[2]),
            'rx_errors' => floatval($parts[3]), 'tx_bytes' => floatval($parts[9]),
            'tx_packets' => floatval($parts[10]), 'tx_errors' => floatval($parts[11])
        ];
    }
}
$result['network'] = $network;

// System
$uptime = @file_get_contents('/proc/uptime');
$uptimeSec = $uptime ? floatval(explode(' ', trim($uptime))[0]) : 0;
$procsStat = @file_get_contents('/proc/stat');
$procsRunning = 0; $procsBlocked = 0;
if ($procsStat) {
    if (preg_match('/procs_running\s+(\d+)/', $procsStat, $m)) $procsRunning = intval($m[1]);
    if (preg_match('/procs_blocked\s+(\d+)/', $procsStat, $m)) $procsBlocked = intval($m[1]);
}
$procDirs = glob('/proc/[0-9]*', GLOB_ONLYDIR);
$numProcs = $procDirs ? count($procDirs) : 0;
$result['system'] = [
    'uptime' => $uptimeSec, 'processes' => $numProcs,
    'procs_running' => $procsRunning, 'procs_blocked' => $procsBlocked,
    'kernel' => php_uname('r'), 'hostname' => gethostname()
];

echo json_encode($result);
