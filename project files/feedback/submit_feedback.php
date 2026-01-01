<?php
session_start();
require_once("../db.php");

/* ---- FETCH ENTITY NAME (TRIGGER REPLACEMENT) ---- */
function getEntityName($conn, $category, $entity_id) {

    switch ($category) {
        case 'Project':
            $sql = "SELECT P_Title AS title FROM project WHERE Project_ID = ? LIMIT 1";
            break;
        case 'Research':
            $sql = "SELECT R_Title AS title FROM research WHERE Research_ID = ? LIMIT 1";
            break;
        case 'Innovation':
            $sql = "SELECT I_Title AS title FROM innovation WHERE Innovation_ID = ? LIMIT 1";
            break;
        case 'Milestone':
            $sql = "SELECT M_Title AS title FROM milestone WHERE Milestone_ID = ? LIMIT 1";
            break;
        default:
            return null;
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("i", $entity_id);
    $stmt->execute();

    $result = $stmt->get_result();
    if (!$result) {
        $stmt->close();
        return null;
    }

    $row = $result->fetch_assoc();
    $stmt->close();

    return $row['title'] ?? null;
}

/* ---- HANDLE FORM SUBMISSION ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_SESSION['user_id'])) {
        die("You must be logged in.");
    }

    $user_id   = $_SESSION['user_id'];
    $review    = trim($_POST['review'] ?? '');
    $f_type    = $_POST['f_type'] ?: NULL;
    $category  = $_POST['category'] ?? '';
    $entity_id = (int)($_POST['entity_id'] ?? 0);

    /* Allowed categories (security) */
    $allowed_categories = ['Project', 'Research', 'Innovation', 'Milestone'];

    if (
        $review === '' ||
        !in_array($category, $allowed_categories, true) ||
        $entity_id <= 0
    ) {
        die("Invalid input.");
    }

    /* 🔁 Trigger logic replacement */
    $entity_name = getEntityName($conn, $category, $entity_id);

    if ($entity_name === null) {
        die("Invalid entity reference.");
    }

    /* Insert feedback */
/* Insert feedback */
$stmt = $conn->prepare(
    "INSERT INTO feedback (User_ID, F_Type, Review, F_Status)
     VALUES (?, ?, ?, 'published')"
);
$stmt->bind_param("iss", $user_id, $f_type, $review);
$stmt->execute();
$feedback_id = $stmt->insert_id;
$stmt->close();

/* Insert feedback relation */
$stmt2 = $conn->prepare(
    "INSERT INTO feedback_relation
     (feedback_id, category, entity_id, entity_name)
     VALUES (?, ?, ?, ?)"
);
$stmt2->bind_param("isis", $feedback_id, $category, $entity_id, $entity_name);
$stmt2->execute();
$stmt2->close();

echo "<p style='color:green;'>Feedback submitted successfully.</p>";

}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Submit Feedback</title>
</head>
<body>

<h3>Leave Feedback</h3>

<form method="POST" action="../feedback/submit_feedback.php">
    <textarea name="review" placeholder="Write feedback..." required></textarea>

    <select name="f_type">
        <option value="">General</option>
        <option value="comment">Comment</option>
        <option value="report">Report</option>
        <option value="suggestion">Suggestion</option>
    </select>

    <!-- 🔥 IMPORTANT CONNECTION -->
    <input type="hidden" name="category" value="Project">
    <input type="hidden" name="entity_id" value="<?= $project_id ?>">

    <button type="submit">Submit</button>
</form>

