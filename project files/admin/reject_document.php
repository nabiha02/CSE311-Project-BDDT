<?php
session_start();
require_once("../db.php");

// Accept both user_role and role (your system uses both in different places)
$user_role = $_SESSION['user_role'] ?? ($_SESSION['role'] ?? '');

// Case-insensitive Admin check
if (strcasecmp($user_role, "Admin") !== 0) {
    die("Access denied.");
}

$doc_id = intval($_GET['id']);

// Update status
$sql = "UPDATE document SET Status='Rejected' WHERE Document_ID=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doc_id);
$stmt->execute();
$stmt->close();

// Get uploader
$q = $conn->prepare("SELECT User_ID FROM document WHERE Document_ID=?");
$q->bind_param("i", $doc_id);
$q->execute();
$q->bind_result($uploader_id);
$q->fetch();
$q->close();

// Notification
$message = "Your document (ID: $doc_id) has been rejected.";

$not = $conn->prepare("
    INSERT INTO notifications (User_ID, Message, Created_At, is_read)
    VALUES (?, ?, NOW(), 0)
");
$not->bind_param("is", $uploader_id, $message);
$not->execute();
$not->close();

header("Location: document_list.php?msg=rejected");
exit;
?>
