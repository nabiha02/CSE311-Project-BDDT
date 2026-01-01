<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once(__DIR__ . "/../dashboard/header.php");
require_once(__DIR__ . "/../db.php");

function h($v) {
    return htmlspecialchars($v ?? "Not Enough Data", ENT_QUOTES, 'UTF-8');
}

// STEP 1 — MUST receive S_Name
if (!isset($_GET['S_Name']) || trim($_GET['S_Name']) == "") {
    die("Invalid sector");
}

$sectorName = trim($_GET['S_Name']);

// STEP 2 — Convert Sector Name → Sector_ID
$stmt = $conn->prepare("SELECT Sector_ID FROM sector WHERE S_Name = ?");
$stmt->bind_param("s", $sectorName);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$res) die("Sector not found.");

$sectorID = (int)$res['Sector_ID'];

/* STEP 3 — Fetch all related items */

$projects = $conn->query("
    SELECT Project_ID AS id, P_Title AS title, 'Project' AS type
    FROM project WHERE Sector_ID = $sectorID
");

$researches = $conn->query("
    SELECT Research_ID AS id, R_Title AS title, 'Research' AS type
    FROM research WHERE Sector_ID = $sectorID
");

$innovations = $conn->query("
    SELECT Innovation_ID AS id, I_Title AS title, 'Innovation' AS type
    FROM innovation WHERE Sector_ID = $sectorID
");

$milestones = $conn->query("
    SELECT Milestone_ID AS id, M_Title AS title, 'Milestone' AS type
    FROM milestone WHERE Sector_ID = $sectorID
");
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= h($sectorName) ?> — Sector</title>
    <link rel="stylesheet" href="index.css">
    <style>
        .section-block {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            border: 1px solid #e5e7eb;
        }
        .item-link {
            display: block;
            padding: 10px;
            border-bottom: 1px solid #eee;
            text-decoration: none;
            color: #0f172a;
            font-weight: 500;
        }
        .item-link:hover {
            background: #f3f4f6;
        }
    </style>
</head>

<body>

<div class="page-content">
    <header class="wrap">
        <h1>Sector — <?= h($sectorName) ?></h1>
        <p class="muted">All projects, research, innovations, and milestones under this sector.</p>
    </header>

    <div class="wrap">

        <!-- Projects -->
       <div class="section-block">
    <div class="section-header">
        <div class="section-title">
            <span class="icon-project">📁</span> Projects
        </div>
        <span class="count-badge"><?= $projects->num_rows ?></span>
    </div>

    <?php if ($projects->num_rows == 0): ?>
        <p class="muted">No projects available.</p>
    <?php else: ?>
        <?php while ($p = $projects->fetch_assoc()): ?>
            <a class="item-link" href="../project/view.php?id=<?= $p['id'] ?>">
                <?= h($p['title']) ?>
                <span class="item-chevron">›</span>
            </a>
        <?php endwhile; ?>
    <?php endif; ?>
</div>


        <!-- Research -->
        <div class="section-block">
    <div class="section-header">
        <div class="section-title">
            <span class="icon-research">🔬</span> Research
        </div>
        <span class="count-badge"><?= $researches->num_rows ?></span>
    </div>

    <?php if ($researches->num_rows == 0): ?>
        <p class="muted">No researches available.</p>
    <?php else: ?>
        <?php while ($r = $researches->fetch_assoc()): ?>
            <a class="item-link" href="../research/view.php?id=<?= $r['id'] ?>">
                <?= h($r['title']) ?>
                <span class="item-chevron">›</span>
            </a>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

        <!-- Innovations -->
        <div class="section-block">
    <div class="section-header">
        <div class="section-title">
            <span class="icon-innovation">💡</span> Innovations
        </div>
        <span class="count-badge"><?= $innovations->num_rows ?></span>
    </div>

    <?php if ($innovations->num_rows == 0): ?>
        <p class="muted">No innovations available.</p>
    <?php else: ?>
        <?php while ($i = $innovations->fetch_assoc()): ?>
            <a class="item-link" href="../innovation/view.php?id=<?= $i['id'] ?>">
                <?= h($i['title']) ?>
                <span class="item-chevron">›</span>
            </a>
        <?php endwhile; ?>
    <?php endif; ?>
</div>


        <!-- Milestones -->
       <div class="section-block">
    <div class="section-header">
        <div class="section-title">
            <span class="icon-milestone">📌</span> Milestones
        </div>
        <span class="count-badge"><?= $milestones->num_rows ?></span>
    </div>

    <?php if ($milestones->num_rows == 0): ?>
        <p class="muted">No milestones available.</p>
    <?php else: ?>
        <?php while ($m = $milestones->fetch_assoc()): ?>
            <a class="item-link" href="../milestone/view.php?id=<?= $m['id'] ?>">
                <?= h($m['title']) ?>
                <span class="item-chevron">›</span>
            </a>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

    </div>

</div>
</body>
</html>
