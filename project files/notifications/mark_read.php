<?php
session_start();
require '../db.php';

if(!isset($_SESSION['user_id']) || !isset($_POST['id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$id = (int) $_POST['id'];
$uid = (int) $_SESSION['user_id'];

$stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE Notification_ID = ? AND User_ID = ?");
$stmt->bind_param("ii", $id, $uid);
$stmt->execute();

echo json_encode(['success' => true]);
