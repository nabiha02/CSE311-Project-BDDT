<?php
session_start();
require_once("../db.php");

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== "Admin") {
    die("Access Denied");
}

$id = intval($_GET['id']);

$sql = $conn->prepare("UPDATE users SET Role='Admin' WHERE User_ID=?");
$sql->bind_param("i", $id);
$sql->execute();

header("Location: users_list.php?msg=admin_made");
exit;
?>
