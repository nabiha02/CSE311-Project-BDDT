<?php
require_once("../db.php");

$category = $_GET['category'] ?? '';
$entity_id = $_GET['entity_id'] ?? '';

if ($category === '' || $entity_id === '') {
    die("Invalid request");
}



$stmt->bind_param("si", $category, $entity_id);
$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    echo "<div class='feedback-box'>";
    echo "<strong>" . htmlspecialchars($row['F_Type']) . "</strong><br>";
    echo "<p>" . nl2br(htmlspecialchars($row['Review'])) . "</p>";
    echo "<small>" . $row['Submitted_Date'] . "</small>";
    echo "</div><hr>";
}

$stmt->close();
