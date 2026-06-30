<?php
header('Content-Type: text/plain');

// Version info
$currentVersion = "1.0.0";
$latestVersion = "1.0.0"; // You can replace this with a dynamic value later

if (version_compare($currentVersion, $latestVersion, '<')) {
    echo "⚠️ Update available!\n\n";
    echo "Current version: v{$currentVersion}\n";
    echo "Latest version: v{$latestVersion}\n\n";
    echo "Please update your system to the latest release.\n\n";
    echo "📞 For assistance, contact the developer:\n";
    echo "Email: hacknet395@gmail.com\n";
    echo "Phone: +63 905 615 2262\n";
} else {
    echo "✅ Your system is up-to-date.\n";
    echo "Version: v{$currentVersion}\n\n";
    echo "📞 For inquiries, contact the developer:\n";
    echo "Email: hacknet395@gmail.com\n";
    echo "Phone: +63 905 615 2262\n";
}
?>
