<?php
session_start();
require '../db.php';

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$docId = intval($_GET['id']);

// Fetch document with uploader info
$stmt = $conn->prepare("
    SELECT d.*, u.F_Name 
    FROM document d
    JOIN users u ON d.User_ID = u.User_ID
    WHERE d.Document_ID = ?
");
$stmt->bind_param("i", $docId);
$stmt->execute();
$doc = $stmt->get_result()->fetch_assoc();

if (!$doc) {
    die("Document not found.");
}

// Fetch document relation info (category + entity_name)
$relStmt = $conn->prepare("
    SELECT category, entity_name 
    FROM document_relation 
    WHERE document_id = ? 
    LIMIT 1
");
$relStmt->bind_param("i", $docId);
$relStmt->execute();
$relation = $relStmt->get_result()->fetch_assoc();
$relStmt->close();

$fileName = $doc['File_Name'];
$fileType = $doc['File_Type'];
$filePath = "/bddt/document/files/" . $fileName; // adjust if needed
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Document</title>
    <link rel="stylesheet" href="view.css">
</head>
<body>

<?php include("../dashboard/header.php"); ?>

<div class="doc-view-container">
    <h2><?= htmlspecialchars($fileName) ?></h2>

    <p><strong>Uploaded By:</strong> <?= htmlspecialchars($doc['F_Name']) ?></p>
    <p><strong>Category:</strong> <?= htmlspecialchars($relation['category'] ?? 'N/A') ?></p>
    <p><strong>Name:</strong> <?= htmlspecialchars($relation['entity_name'] ?? 'N/A') ?></p>
    <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($doc['D_Description'])) ?></p>
    <p><strong>Uploaded At:</strong> <?= $doc['D_Uploaded_At'] ?></p>

    <div class="actions">
        <a href="<?= $filePath ?>" download class="download-btn">Download File</a>

        <?php if (in_array($fileType, ['image/png','image/jpeg','image/jpg','image/webp'])): ?>
            <div class="image-preview">
                <img src="<?= $filePath ?>" alt="Document Preview" style="max-width:800px;">
            </div>
        <?php else: ?>
            <p>No preview available. Download to view.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
