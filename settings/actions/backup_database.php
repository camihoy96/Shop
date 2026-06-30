<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'st4nger';
$mysqldump = "D:\\xampp\\mysql\\bin\\mysqldump.exe";

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="backup_' . date('Y-m-d_H-i-s') . '.sql"');
header('Cache-Control: no-cache');
header('Pragma: public');

// ✅ Stream directly to browser (no intermediate file)
passthru("\"$mysqldump\" --host=$host --user=$username --password=$password $database");

exit;
?>
