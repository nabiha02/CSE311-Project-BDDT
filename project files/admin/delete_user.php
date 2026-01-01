<?php
session_start();
require_once("../db.php");

$user_role = $_SESSION['user_role'] ?? '';
if (strcasecmp($user_role,"Admin")!==0) { echo json_encode(['status'=>'error','message'=>'Access denied']); exit; }

$feedback_id = (int)($_POST['feedback_id'] ?? 0);
if ($feedback_id <= 0) { echo json_encode(['status'=>'error','message'=>'Invalid ID']); exit; }

$conn->begin_transaction();
try {
    $stmt = $conn->prepare("DELETE FROM feedback_relation WHERE feedback_id=?");
    $stmt->bind_param("i",$feedback_id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM feedback WHERE Feedback_ID=?");
    $stmt->bind_param("i",$feedback_id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    echo json_encode(['status'=>'success']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
// NO closing PHP tag
