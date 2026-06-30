<?php
// ===============================
// 🩺 SYSTEM HEALTH CHECK SCRIPT
// ===============================

header('Content-Type: text/plain');

$report = [];

// ✅ Check PHP version
$phpVersion = phpversion();
$report[] = "PHP Version: $phpVersion";

// ✅ Check MySQL connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "st4nger";

$conn = @new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    $report[] = "❌ MySQL Connection: FAILED (" . $conn->connect_error . ")";
} else {
    $report[] = "✅ MySQL Connection: OK";
}

// ✅ Check important directories
$dirs = ['backups', 'cache', 'uploads'];
foreach ($dirs as $dir) {
    if (!file_exists("../$dir")) {
        $report[] = "⚠️ Directory missing: $dir";
    } elseif (!is_writable("../$dir")) {
        $report[] = "⚠️ Directory not writable: $dir";
    } else {
        $report[] = "✅ Directory OK: $dir";
    }
}

// ✅ Check disk space
$free = round(disk_free_space("/") / 1024 / 1024, 2);
$total = round(disk_total_space("/") / 1024 / 1024, 2);
$used = $total - $free;
$report[] = "💾 Disk Usage: $used MB / $total MB";

// ✅ Check memory usage
$memory = round(memory_get_usage(true) / 1024 / 1024, 2);
$report[] = "🧠 PHP Memory Usage: {$memory} MB";

// ✅ Check server uptime (Linux only)
$uptime = @shell_exec("uptime -p");
if ($uptime) {
    $report[] = "⏱️ Server Uptime: " . trim($uptime);
}

// ✅ Final result
echo implode("\n", $report);
?>
