<?php
require_once("../db.php");

$category = $_GET['category'] ?? '';
$data = [];

if ($category) {
    switch($category) {
        case 'Project':
            $stmt = $conn->prepare("SELECT Project_ID as id, P_Title as title FROM project");
            break;
        case 'Research':
            $stmt = $conn->prepare("SELECT Research_ID as id, R_Title as title FROM research");
            break;
        case 'Innovation':
            $stmt = $conn->prepare("SELECT Innovation_ID as id, I_Title as title FROM innovation");
            break;
        case 'Milestone':
            $stmt = $conn->prepare("SELECT Milestone_ID as id, M_Title as title FROM milestone");
            break;
    }
    if(isset($stmt)){
        $stmt->execute();
        $res = $stmt->get_result();
        while($row = $res->fetch_assoc()){
            $data[] = $row;
        }
    }
}

echo json_encode($data);
?>
