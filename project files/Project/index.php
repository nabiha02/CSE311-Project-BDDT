<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once(__DIR__ . "/../dashboard/header.php");
require_once(__DIR__ . "/../db.php");

$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : "";

if ($statusFilter !== "") {
    $query = $conn->prepare("
        SELECT Project_ID, P_Title, P_Status, P_Start_Date , P_Publish_Date
        FROM project 
        WHERE P_Status = ?
        ORDER BY Project_ID DESC
    ");
    $query->bind_param("s", $statusFilter);
    $query->execute();
    $projects = $query->get_result();

} else {
    $projects = $conn->query("
        SELECT Project_ID, P_Title, P_Status, P_Start_Date, P_Publish_Date
        FROM project ORDER BY Project_ID DESC
    ");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Projects</title>
    <link rel="stylesheet" href="index.css">
</head>

<body>

<div class="page-content">

<header class="wrap">
    <h1>BDDT — Projects</h1>
    <p class="muted">Click a Project to view full details</p>
</header>

<div class="wrap grid">

<?php while ($p = $projects->fetch_assoc()): ?>

    <?php
        // Start date fix
        $start = $p['P_Start_Date'];
        $start_display = ($start === "0000-00-00" || !$start) ? "Not Enough Data" : $start;
        

         $publish = $p['P_Publish_Date'];
        $publish_display = ($publish === "0000-00-00" || !$publish) ? "Not Published Yet" : $publish;
        // Badge class
        $cls = strtolower(str_replace(" ", "-", trim($p['P_Status'])));
        $badgeClass = "status-" . $cls;
    ?>

    <a class="project-card" href="view.php?id=<?= $p['Project_ID'] ?>">

        <div class="project-left">
            <h2 class="project-title"><?= htmlspecialchars($p['P_Title']) ?></h2>

            <div class="meta-item">
                📅 <strong>Start:</strong> <?= htmlspecialchars($start_display) ?>
            </div>
            <div class="meta-item">
                📰 <strong>Published:</strong> <?= htmlspecialchars($publish_display) ?>
            </div>
        </div>

        <!-- RIGHT SIDE BADGE -->
        <span class="status-badge <?= $badgeClass ?>">
            <?= htmlspecialchars($p['P_Status']) ?>
        </span>

    </a>

<?php endwhile; ?>

</div>
</div>

</body>
</html>
