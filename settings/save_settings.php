<?php
require 'dbconn.php';
if (isset($_POST['maintenance_mode'])) {
    $mode = intval($_POST['maintenance_mode']);
    $conn->query("UPDATE site_settings SET maintenance_mode = $mode WHERE id = 1");
    echo "success";
}
?>
