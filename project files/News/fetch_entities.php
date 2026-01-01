<?php
session_start();
require_once("../db.php");

$cat = $_GET["cat"];

switch ($cat) {
    case "project":
        $sql = "SELECT Project_ID AS id, P_Title AS title FROM project";
        break;
    case "research":
        $sql = "SELECT Research_ID AS id, R_Title AS title FROM research";
        break;
    case "innovation":
        $sql = "SELECT Innovation_ID AS id, I_Title AS title FROM innovation";
        break;
    case "milestone":
        $sql = "SELECT Milestone_ID AS id, M_Title AS title FROM milestone";
        break;
}

$result = $conn->query($sql);
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>
