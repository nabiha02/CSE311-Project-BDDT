<?php
session_start();
include_once(__DIR__ . "/../dashboard/header.php");
require_once("../db.php");

// Admin check
$user_role = $_SESSION['user_role'] ?? ($_SESSION['role'] ?? '');


if (strcasecmp($user_role, "Admin") !== 0) {
    die("Access denied.");
}

$feedback_id = (int)$_POST['feedback_id'];


$rel_stmt = $conn->prepare("DELETE FROM feedback_relation WHERE feedback_id = ?");
$rel_stmt->bind_param("i", $feedback_id);
$rel_stmt->execute();
$rel_stmt->close();

// Delete main feedback
$fb_stmt = $conn->prepare("DELETE FROM feedback WHERE Feedback_ID = ?");
$fb_stmt->bind_param("i", $feedback_id);
$fb_stmt->execute();
$fb_stmt->close();


echo json_encode(['status'=>'success']);
exit;
?>
