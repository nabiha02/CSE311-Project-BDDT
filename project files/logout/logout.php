<?php
session_start();
session_unset();
session_destroy();
header("Location: /bddt/dashboard/index.php");
exit;
?>
