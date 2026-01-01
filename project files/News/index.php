<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once(__DIR__ . "/../dashboard/header.php");
require_once(__DIR__ . "/../db.php");

// Fetch news + category
$news = $conn->query("
    SELECT n.News_ID, n.N_Title, n.N_Publish_Date,
           nr.category
    FROM news n
    LEFT JOIN news_relation nr ON n.News_ID = nr.news_id
    ORDER BY n.News_ID DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>News</title>
    <link rel="stylesheet" href="index.css">
</head>

<body>

<div class="page-content">

<header class="wrap">
    <h1>BDDT — News</h1>
    <p class="muted">Click a news article to view details</p>
</header>

<div class="wrap grid">

<?php while ($n = $news->fetch_assoc()): ?>

    <?php
        $date = $n['N_Publish_Date'];
        $date_display = ($date === "0000-00-00" || !$date) ? "Not Enough Data" : $date;

        // Category badge
        $cat = strtolower(trim($n['category'] ?? "general"));
        $badgeClass = "status-" . $cat;
    ?>

    <a class="project-card" href="view.php?id=<?= $n['News_ID'] ?>">

        <div class="project-left">
            <h2 class="project-title"><?= htmlspecialchars($n['N_Title']) ?></h2>
            <div class="meta-item">
                📅 <strong>Published:</strong> <?= htmlspecialchars($date_display) ?>
            </div>
        </div>

        <span class="status-badge <?= $badgeClass ?>">
            <?= htmlspecialchars($n['category'] ?? "General") ?>
        </span>

    </a>

<?php endwhile; ?>

</div>
</div>

</body>
</html>
