<?php
session_start();
require_once(__DIR__ . "/../db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/auth.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? ($_SESSION['role'] ?? '');
include_once(__DIR__ . "/../dashboard/header.php");

// Anyone can see approved documents
$stmt = $conn->prepare("
    SELECT d.*, r.category, r.entity_name, u.F_Name 
    FROM document d
    LEFT JOIN document_relation r ON r.document_id = d.Document_ID
    LEFT JOIN users u ON u.User_ID = d.User_ID
    WHERE d.Status = 'Approved'
    ORDER BY d.D_Uploaded_At DESC
");
$stmt->execute();
$q = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Approved Documents</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>

<div class="doc-container">
    <h2>Approved Documents</h2>

    <div class="list">
        <?php while ($row = $q->fetch_assoc()): ?>
            <div class="doc-card approved">
                <div class="doc-left">
                    <h3><?= htmlspecialchars($row['File_Name']) ?></h3>
                    <p class="meta"><?= htmlspecialchars($row['category'] ?? '') ?> — <?= htmlspecialchars($row['entity_name'] ?? '') ?></p>
                    <p class="descr"><?= nl2br(htmlspecialchars($row['D_Description'] ?? '')) ?></p>
                </div>

                <div class="doc-right">
                    <a class="view" href="files/<?= htmlspecialchars($row['File_Name']) ?>" target="_blank">View / Download</a>
                    <p class="by">Uploaded By: <?= htmlspecialchars($row['F_Name'] ?? 'Unknown') ?></p>
                    <p class="date"><?= htmlspecialchars($row['D_Uploaded_At']) ?></p>
                    <p class="status">Approved</p>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

</body>
</html>
