<?php
// ===============================
// 🧹 CLEAR CACHE SCRIPT
// ===============================

header('Content-Type: text/plain');

$cacheDirs = ['cache', 'temp', 'tmp', 'backups/tmp']; // Add paths as needed
$deleted = 0;
$errors = 0;

foreach ($cacheDirs as $dir) {
    if (is_dir($dir)) {
        $files = glob("$dir/*");
        foreach ($files as $file) {
            if (is_file($file)) {
                if (@unlink($file)) {
                    $deleted++;
                } else {
                    $errors++;
                }
            }
        }
    }
}

if ($deleted > 0) {
    echo "✅ Cleared $deleted cached file(s).";
} elseif ($errors > 0) {
    echo "⚠️ Some files couldn't be deleted ($errors error(s)). Check permissions.";
} else {
    echo "ℹ️ No cache files found or nothing to clear.";
}
?>
