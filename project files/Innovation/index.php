<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once(__DIR__ . "/../dashboard/header.php");
require_once(__DIR__ . "/../db.php");

$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : "";

if ($statusFilter !== "") {
    $query = $conn->prepare("
        SELECT Innovation_ID, I_Title, I_Status, I_Publish_Date
        FROM innovation
        WHERE I_Status = ?
        ORDER BY Innovation_ID DESC
    ");
    $query->bind_param("s", $statusFilter);
    $query->execute();
    $items = $query->get_result();
} else {
    $items = $conn->query("
        SELECT Innovation_ID, I_Title, I_Status, I_Publish_Date
        FROM innovation ORDER BY Innovation_ID DESC
    ");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Innovation</title>
    <link rel="stylesheet" href="index.css">
</head>

<body>

<div class="page-content">

<header class="wrap">
    <h1>BDDT — Innovation</h1>
    <p class="muted">Click an innovation to view full details</p>
</header>

<div class="wrap grid">

<?php while ($r = $items->fetch_assoc()): ?>

    <?php
        $publish = $r["I_Publish_Date"] ?? "";
        $publish_display = ($publish === "0000-00-00" || !$publish) ? "Not Enough Data" : $publish;

        $cls = strtolower(str_replace(" ", "-", trim($r["I_Status"] ?? "")));
        $badgeClass = "status-" . $cls;
    ?>

    <a class="project-card" href="view.php?id=<?= (int)$r['Innovation_ID'] ?>">

        <div class="project-left">
            <h2 class="project-title"><?= htmlspecialchars($r["I_Title"] ?? "Untitled") ?></h2>

            <div class="meta-item">
                📅 <strong>Published:</strong> <?= htmlspecialchars($publish_display) ?>
            </div>
        </div>

        <span class="status-badge <?= $badgeClass ?>">
            <?= htmlspecialchars($r["I_Status"] ?? "Unknown") ?>
        </span>

    </a>

<?php endwhile; ?>

</div>
</div>

</body>
</html>
