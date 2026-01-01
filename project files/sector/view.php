<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . "/../dashboard/header.php");
require_once(__DIR__ . "/../db.php");

function h($v) { return htmlspecialchars($v ?? "Not Enough Data", ENT_QUOTES, 'UTF-8'); }

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Sector ID");
}

$sid = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM sector WHERE Sector_ID = ?");
$stmt->bind_param("i", $sid);
$stmt->execute();
$sector = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$sector) die("Sector not found.");


// ---------------- RELATED PROJECTS ----------------
$pq = $conn->prepare("SELECT Project_ID, P_Title, P_Status FROM project WHERE Sector_ID = ?");
$pq->bind_param("i", $sid);
$pq->execute();
$projects = $pq->get_result()->fetch_all(MYSQLI_ASSOC);
$pq->close();


// ---------------- RELATED RESEARCH ----------------
$rq = $conn->prepare("SELECT Research_ID, R_Title, R_Status FROM research WHERE Sector_ID = ?");
$rq->bind_param("i", $sid);
$rq->execute();
$researches = $rq->get_result()->fetch_all(MYSQLI_ASSOC);
$rq->close();


// ---------------- RELATED INNOVATION ----------------
$iq = $conn->prepare("SELECT Innovation_ID, I_Title, I_Status FROM innovation WHERE Sector_ID = ?");
$iq->bind_param("i", $sid);
$iq->execute();
$innovations = $iq->get_result()->fetch_all(MYSQLI_ASSOC);
$iq->close();


// ---------------- RELATED MILESTONES ----------------
$mq = $conn->prepare("SELECT Milestone_ID, M_Title, M_Status FROM milestone WHERE Sector_ID = ?");
$mq->bind_param("i", $sid);
$mq->execute();
$milestones = $mq->get_result()->fetch_all(MYSQLI_ASSOC);
$mq->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= h($sector['S_Name']) ?></title>
    <link rel="stylesheet" href="index.css">

    <style>
        .page-content { margin-top: 70px; }
    </style>
</head>

<body>

<div class="page-content">

<a href="index.php" class="back">&larr; Back to Sectors</a>

<h1 class="detail-title"><?= h($sector['S_Name']) ?></h1>

<div class="detail-container">

    <div class="detail-left">

        <div class="info-card">
            <div class="info-header">
                <span class="info-icon">📌</span>
                <h2>Sector Overview</h2>
            </div>

            <p>This sector includes all related projects, research, innovations and milestones.</p>
        </div>

        <!-- PROJECTS -->
        <div class="info-card">
            <div class="info-header"><span class="info-icon">📁</span><h2>Projects</h2></div>

            <?php if (empty($projects)): ?>
                <p class="empty-text">No projects in this sector.</p>
            <?php else: ?>
                <?php foreach ($projects as $p): ?>
                    <div class="list-item">
                        <a href="../project/view.php?id=<?= $p['Project_ID'] ?>">
                            <strong><?= h($p['P_Title']) ?></strong>
                        </a>
                        <br><span class="muted"><?= h($p['P_Status']) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

    <div class="detail-right">

        <!-- RESEARCH -->
        <div class="info-card">
            <div class="info-header"><span class="info-icon">🔬</span><h2>Research</h2></div>

            <?php if (empty($researches)): ?>
                <p class="empty-text">No research items in this sector.</p>
            <?php else: ?>
                <?php foreach ($researches as $r): ?>
                    <div class="list-item">
                        <a href="../research/view.php?id=<?= $r['Research_ID'] ?>">
                            <strong><?= h($r['R_Title']) ?></strong>
                        </a>
                        <br><span class="muted"><?= h($r['R_Status']) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- INNOVATION -->
        <div class="info-card">
            <div class="info-header"><span class="info-icon">💡</span><h2>Innovations</h2></div>

            <?php if (empty($innovations)): ?>
                <p class="empty-text">No innovations in this sector.</p>
            <?php else: ?>
                <?php foreach ($innovations as $i): ?>
                    <div class="list-item">
                        <a href="../innovation/view.php?id=<?= $i['Innovation_ID'] ?>">
                            <strong><?= h($i['I_Title']) ?></strong>
                        </a>
                        <br><span class="muted"><?= h($i['I_Status']) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- MILESTONES -->
        <div class="info-card">
            <div class="info-header"><span class="info-icon">🏁</span><h2>Milestones</h2></div>

            <?php if (empty($milestones)): ?>
                <p class="empty-text">No milestones in this sector.</p>
            <?php else: ?>
                <?php foreach ($milestones as $m): ?>
                    <div class="list-item">
                        <a href="../milestone/view.php?id=<?= $m['Milestone_ID'] ?>">
                            <strong><?= h($m['M_Title']) ?></strong>
                        </a>
                        <br><span class="muted"><?= h($m['M_Status']) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

</div>

</div>

</body>
</html>
