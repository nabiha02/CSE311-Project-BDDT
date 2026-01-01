<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . "/../dashboard/header.php");
require_once(__DIR__ . "/../db.php");

function h($v) { return htmlspecialchars($v ?? "Not Enough Data", ENT_QUOTES, 'UTF-8'); }

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid News ID");
}

$nid = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM news WHERE News_ID = ?");
$stmt->bind_param("i", $nid);
$stmt->execute();
$news = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$news) die("News not found.");

$publish_display = ($news['N_Publish_Date'] === "0000-00-00" || !$news['N_Publish_Date'])
    ? "Not Enough Data"
    : $news['N_Publish_Date'];

/* ------------------------- RELATIONS ------------------------- */
$rel_stmt = $conn->prepare("
    SELECT category, entity_id, entity_name
    FROM news_relation
    WHERE news_id = ?
");
$rel_stmt->bind_param("i", $nid);
$rel_stmt->execute();
$relations = $rel_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$rel_stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= h($news['N_Title']) ?></title>
    <link rel="stylesheet" href="index.css">

    <style>
        .page-content { margin-top: 70px; }
    </style>
</head>

<body>

<div class="page-content">

<a href="index.php" class="back">&larr; Back to News</a>

<h1 class="detail-title"><?= h($news['N_Title']) ?></h1>

<div class="detail-container">

    <!-- LEFT SIDE -->
    <div class="detail-left">
        <div class="info-card">
            <div class="info-header">
                <span class="info-icon">📰</span>
                <h2>News Details</h2>
            </div>

            <div class="info-grid">
                <div><strong>Publish Date:</strong> <?= h($publish_display) ?></div>
            </div>

            <h3>Full Content</h3>
            <div class="description-box">
                <?= nl2br(h($news['N_Content'])) ?>
            </div>
        </div>
    </div>


    <!-- RIGHT SIDE — RELATED ENTITIES -->
    <div class="detail-right">

        <div class="info-card">
            <div class="info-header">
                <span class="info-icon">📌</span>
                <h2>Related To:</h2>
            </div>

            <?php if (empty($relations)): ?>
                <p class="empty-text">No related entities.</p>
            <?php else: ?>
                <?php foreach ($relations as $rl): ?>

                    <?php
                        $cat = strtolower(trim($rl['category']));
                        $id  = $rl['entity_id'];

                        switch ($cat) {
                            case "project":
                                $link = "../project/view.php?id=" . $id;
                                break;
                            case "research":
                                $link = "../research/view.php?id=" . $id;
                                break;
                            case "innovation":
                                $link = "../innovation/view.php?id=" . $id;
                                break;
                            case "milestone":
                                $link = "../milestone/view.php?id=" . $id;
                                break;
                            default:
                                $link = "#";
                        }
                    ?>

                    <div class="list-item">
                        <a href="<?= $link ?>" class="entity-link">
                            <strong><?= h($rl['entity_name']) ?></strong>
                        </a><br><br>
                        Click above to read about the related
                        <span class="muted"><?= h(ucfirst($rl['category'])) ?></span> ⤴️
                    </div>

                <?php endforeach; ?>
            <?php endif; ?>

        </div>

    </div>

</div>

</div>

</body>
</html>
