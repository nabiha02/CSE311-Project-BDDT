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

// Fetch documents
if (strcasecmp($user_role, 'Admin') === 0) {
    $q = $conn->query("
        SELECT d.*, r.category, r.entity_id, r.entity_name, u.F_Name 
        FROM document d
        LEFT JOIN document_relation r ON r.document_id = d.Document_ID
        LEFT JOIN users u ON u.User_ID = d.User_ID
        ORDER BY d.D_Uploaded_At DESC
    ");
} else {
    $stmt = $conn->prepare("
        SELECT d.*, r.category, r.entity_id, r.entity_name, u.F_Name 
        FROM document d
        LEFT JOIN document_relation r ON r.document_id = d.Document_ID
        LEFT JOIN users u ON u.User_ID = d.User_ID
        WHERE d.User_ID = ?
        ORDER BY d.D_Uploaded_At DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $q = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Documents</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>

<div class="doc-container">
    <h2>Documents</h2>
    <a class="btn-upload" href="upload.php">Upload New Document</a>

    <?php if (isset($_GET['upload'])): ?>
        <p class="success">Upload successful.</p>
    <?php endif; ?>

    <div class="list">
        <?php while ($row = $q->fetch_assoc()): ?>
            <?php
                // Fallback: fetch entity_name if missing
                if (empty($row['entity_name']) && !empty($row['entity_id'])) {
                    switch ($row['category']) {
                        case 'Project':
                            $res = $conn->query("SELECT P_Title FROM project WHERE Project_ID=".(int)$row['entity_id']);
                            $row['entity_name'] = $res->fetch_assoc()['P_Title'] ?? '';
                            break;
                        case 'Research':
                            $res = $conn->query("SELECT R_Title FROM research WHERE Research_ID=".(int)$row['entity_id']);
                            $row['entity_name'] = $res->fetch_assoc()['R_Title'] ?? '';
                            break;
                        case 'Innovation':
                            $res = $conn->query("SELECT I_Title FROM innovation WHERE Innovation_ID=".(int)$row['entity_id']);
                            $row['entity_name'] = $res->fetch_assoc()['I_Title'] ?? '';
                            break;
                        case 'Milestone':
                            $res = $conn->query("SELECT M_Title FROM milestone WHERE Milestone_ID=".(int)$row['entity_id']);
                            $row['entity_name'] = $res->fetch_assoc()['M_Title'] ?? '';
                            break;
                    }
                }
            ?>
            <div class="doc-card <?= strtolower($row['Status']) ?>">
                <div class="doc-left">
                    <h3><?= htmlspecialchars($row['File_Name']) ?></h3>
                    <p class="meta"><?= htmlspecialchars($row['category'] ?? '') ?> — <?= htmlspecialchars($row['entity_name'] ?? '') ?></p>
                    <p class="descr"><?= nl2br(htmlspecialchars($row['D_Description'] ?? '')) ?></p>
                </div>
                <div class="doc-right">
                    <a class="view" href="view.php?id=<?= (int)$row['Document_ID'] ?>">View / Download</a>

                    <p class="by">By: <?= htmlspecialchars($row['F_Name'] ?? 'Unknown') ?></p>
                    <p class="date"><?= htmlspecialchars($row['D_Uploaded_At']) ?></p>
                    <p class="status"><?= htmlspecialchars($row['Status']) ?></p>

                    <?php if (strcasecmp($user_role, 'Admin') === 0 && $row['Status'] === 'Pending'): ?>
                        <div style="margin-top:8px;">
                            <a class="btn-approve" href="../admin/approve_document.php?id=<?= (int)$row['Document_ID'] ?>">Approve</a>
                            <a class="btn-reject" href="../admin/reject_document.php?id=<?= (int)$row['Document_ID'] ?>">Reject</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

</body>
</html>
