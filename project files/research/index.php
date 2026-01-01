<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once(__DIR__ . "/../dashboard/header.php");
require_once(__DIR__ . "/../db.php");

$statusFilter = $_GET['status'] ?? "";

if ($statusFilter !== "") {
    $stmt = $conn->prepare("
        SELECT Research_ID, R_Title, R_Status, R_Start_Date, R_Publish_Date
        FROM research
        WHERE R_Status = ?
        ORDER BY Research_ID DESC
    ");
    $stmt->bind_param("s", $statusFilter);
    $stmt->execute();
    $researches = $stmt->get_result();
} else {
    $researches = $conn->query("
        SELECT Research_ID, R_Title, R_Status, R_Start_Date, R_Publish_Date
        FROM research ORDER BY Research_ID DESC
    ");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Research</title>
    <link rel="stylesheet" href="index.css">
</head>

<body>

<div class="page-content">

<header class="wrap">
    <h1>BDDT — Research</h1>
    <p class="muted">Click a Research item to view full details</p>
</header>

<div class="wrap grid">

<?php while ($r = $researches->fetch_assoc()): ?>

    <?php
        $start = $r['R_Start_Date'];
        $start_display = ($start === "0000-00-00" || !$start) ? "Not Enough Data" : $start;
        
        $publish = $r['R_Publish_Date'];
        $publish_display = ($publish === "0000-00-00" || !$publish) ? "Not Enough Data" : $publish;

        $cls = strtolower(str_replace(" ", "-", trim($r['R_Status'])));
        $badgeClass = "status-" . $cls;
    ?>

    <a class="project-card" href="view.php?id=<?= $r['Research_ID'] ?>">

        <div class="project-left">
            <h2 class="project-title"><?= htmlspecialchars($r['R_Title']) ?></h2>

            <div class="meta-item">
                📅 <strong>Started:</strong> <?= htmlspecialchars($start_display) ?>
            </div>

            <div class="meta-item">
                📰 <strong>Published:</strong> <?= htmlspecialchars($publish_display) ?>
            </div>
        </div>

        <span class="status-badge <?= $badgeClass ?>">
            <?= htmlspecialchars($r['R_Status']) ?>
        </span>

    </a>

<?php endwhile; ?>

</div>
</div>

</body>
</html>
