<?php
session_start();
session_destroy();
header("Location: logout_transition.php");
exit;
?>