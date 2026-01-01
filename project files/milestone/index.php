<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once(__DIR__ . "/../dashboard/header.php");
require_once(__DIR__ . "/../db.php");

// Fetch all milestones
$milestones = $conn->query("
    SELECT Milestone_ID, M_Title, M_Start_Date,M_Target_Date
    FROM milestone
    ORDER BY Milestone_ID DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Milestones</title>
    <link rel="stylesheet" href="index.css">
</head>

<body>

<div class="page-content">

<header class="wrap">
    <h1>BDDT — Milestones</h1>
    <p class="muted">Click a milestone to view full details</p>
</header>

<div class="wrap grid">

<?php while ($m = $milestones->fetch_assoc()): ?>

    <?php
        $start = $m['M_Start_Date'];
        $start_display = ($start === "0000-00-00" || !$start) ? "Not Enough Data" : $start;
        $target = $m['M_Target_Date'];
        $target_display = ($target === "0000-00-00" || !$target) ? "Not Enough Data" : $target;
    ?>

    <a class="project-card" href="view.php?id=<?= $m['Milestone_ID'] ?>">

        <div class="project-left">
            <h2 class="project-title"><?= htmlspecialchars($m['M_Title']) ?></h2>

            <div class="meta-item">
                📅 <strong>Start:</strong> <?= htmlspecialchars($start_display) ?>
            </div>
            <div class="meta-item">
                ✏️ <strong>Completion:</strong> <?= htmlspecialchars($target_display) ?>
            </div>
        </div>

    </a>

<?php endwhile; ?>

</div>
</div>

</body>
</html>
