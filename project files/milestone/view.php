<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . "/../dashboard/header.php");
require_once(__DIR__ . "/../db.php");

function h($value) {
    return htmlspecialchars($value ?? "Not Enough Data", ENT_QUOTES, 'UTF-8');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Milestone ID");
}

$mid = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM milestone WHERE Milestone_ID = ?");
$stmt->bind_param("i", $mid);
$stmt->execute();
$milestone = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$milestone) die("Milestone not found.");

// Format dates
$start_display = ($milestone['M_Start_Date'] === "0000-00-00" || !$milestone['M_Start_Date'])
                 ? "Not Enough Data" : $milestone['M_Start_Date'];

$target_display = ($milestone['M_Target_Date'] === "0000-00-00" || !$milestone['M_Target_Date'])
                 ? "Not Enough Data" : $milestone['M_Target_Date'];

// Get Sector
$sector_name = "Not Enough Data";
if (!empty($milestone['Sector_ID'])) {
    $sec = $conn->prepare("SELECT S_Name FROM sector WHERE Sector_ID = ?");
    $sec->bind_param("i", $milestone['Sector_ID']);
    $sec->execute();
    $res = $sec->get_result()->fetch_assoc();
    if ($res) $sector_name = $res['S_Name'];
    $sec->close();
}

/* ================= PROJECT DOCUMENTS ================= */
$doc_stmt = $conn->prepare("
    SELECT d.Document_ID, d.File_Name, d.D_Uploaded_At
    FROM document d
    JOIN document_relation r ON r.document_id = d.Document_ID
    WHERE r.category = 'Milestone'
      AND r.entity_id = ?
      AND d.Status = 'Approved'
    ORDER BY d.D_Uploaded_At DESC
");

if (!$doc_stmt) {
    die("Document query failed: " . $conn->error);
}

$doc_stmt->bind_param("i", $pid);
$doc_stmt->execute();
$documents = $doc_stmt->get_result();

$new_stmt = $conn->prepare("
    SELECT n.News_ID, n.N_Title, n.N_Publish_Date
    FROM news n
    JOIN news_relation nr ON nr.news_id = n.News_ID
    WHERE nr.category = 'Milestone'
      AND nr.entity_id = ?
    ORDER BY n.N_Publish_Date DESC
");

$new_stmt->bind_param("i", $mid); // ✅ use Milestone ID
$new_stmt->execute();
$news = $new_stmt->get_result();


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= h($milestone['M_Title']) ?></title>
    <link rel="stylesheet" href="index.css">
</head>

<body>

<div class="page-content">

<a href="index.php" class="back">&larr; Back to Milestones</a>

<h1 class="detail-title"><?= h($milestone['M_Title']) ?></h1>

<div class="detail-container">

    <div class="detail-left">

        <div class="info-card">
            <div class="info-header">
                <span class="info-icon">🎯</span>
                <h2>Milestone Overview</h2>
            </div>

            <div class="info-grid">
                <div><strong>Start Date:</strong> <?= h($start_display) ?></div>
                <div><strong>Target Date:</strong> <?= h($target_display) ?></div>
                <div><strong>Budget:</strong> <?= h($milestone['M_Budget']) ?></div>
                <div><strong>Cost:</strong> <?= h($milestone['M_Cost']) ?></div>
                <div><strong>Sector:</strong> <?= h($sector_name) ?></div>
            </div>

            <h3>Description</h3>
            <div class="description-box">
                <?= nl2br(h($milestone['M_Description'])) ?>
            </div>

        </div>
     </div>
     <div class="detail-right">
        <div class="info-card mt-4">
    <div class="info-header">
        <span class="info-icon">📄</span>
        <h2>Milestone Documents</h2>
    </div>

    <?php if ($documents->num_rows > 0): ?>
        <?php while ($doc = $documents->fetch_assoc()): ?>
            <div class="list-item">
                <a href="../document/view.php?id=<?= (int)$doc['Document_ID'] ?>">
                    <?= h($doc['File_Name']) ?>
                </a>
                <p class="muted">
                    <?= date("d M Y", strtotime($doc['D_Uploaded_At'])) ?>
                </p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="empty-text">No documents uploaded for this.</p>
    <?php endif; ?>

    <a href="http://localhost/bddt/document/upload.php"
       class="btn btn-sm btn-primary mt-2">
        Upload Document
    </a>

    </div>
    <div class="info-card mt-4">
    <div class="info-header">
        <span class="info-icon">🗞️</span>
        <h2>News</h2>
    </div>

    <?php if ($news->num_rows > 0): ?>
        <?php while ($new = $news->fetch_assoc()): ?>
            <div class="list-item">
                <a href="../news/view.php?id=<?= (int)$new['News_ID'] ?>">
                    <?= h($new['N_Title']) ?>
                </a>
                <p class="muted">
                    <?= date("d M Y", strtotime($new['N_Publish_Date'])) ?>
                </p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="empty-text">No news uploaded for this</p>
    <?php endif; ?>

    <a href="http://localhost/bddt/news/add.php"
       class="btn btn-sm btn-primary mt-2">
        Upload a News
    </a>
    </div>

    <div class="info-card mt-4">
    <div class="info-header">
        <span class="info-icon">💬</span>
        <h2>Leave Feedback</h2>
    </div>

    <form id="feedbackForm" method="POST" action="../feedback/submit_feedback.php">

        <textarea name="review" placeholder="Write your feedback..." required></textarea>

        <select name="f_type">
            <option value="">General</option>
            <option value="comment">Comment</option>
            <option value="report">Report</option>
            <option value="suggestion">Suggestion</option>
        </select>

        <!-- Hidden inputs tell feedback system which project this is -->
        <input type="hidden" name="category" value="Milestone">
        <input type="hidden" name="entity_id" value="<?= (int)$mid ?>">

        <button type="submit">Submit Feedback</button>
    </form>
</div>
<div class="info-card mt-4">
    <div class="info-header">
        <span class="info-icon">📝</span>
        <h2>Feedback</h2>
    </div>

    <?php
$fb_stmt = $conn->prepare("
    SELECT f.Review, f.F_Type, f.Submitted_Date, f.User_ID
    FROM feedback f
    JOIN feedback_relation fr ON f.Feedback_ID = fr.feedback_id
    WHERE fr.category = 'Milestone'
      AND fr.entity_id = ?
      AND f.F_Status = 'published'
    ORDER BY f.Submitted_Date DESC
");
$fb_stmt->bind_param("i", $mid);
$fb_stmt->execute();
$feedbacks = $fb_stmt->get_result();

if ($feedbacks->num_rows > 0):
    while ($fb = $feedbacks->fetch_assoc()):
?>
    <div class="list-item feedback-box">
        <strong><?= htmlspecialchars($fb['F_Type'] ?? 'General') ?></strong>
        by <em>UserId-<?= htmlspecialchars($fb['User_ID']) ?></em>
        <p><?= nl2br(htmlspecialchars($fb['Review'])) ?></p>
        <small><?= $fb['Submitted_Date'] ?></small>
    </div>
    <hr>
<?php
    endwhile;
else:
    echo "<p class='empty-text'>No feedback yet. Be the first to comment!</p>";
endif;
$fb_stmt->close();
?>

</div>

</div>

</div>
<?php $doc_stmt->close(); ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function loadFeedback() {
    $.get('../feedback/fetch_feedback.php', {
        category: 'Milestone',
        entity_id: <?= $mid ?>
    }, function(data) {
        $('#feedbackList').html(data);
    });
}

$('#feedbackForm').on('submit', function(e){
    e.preventDefault();
    $.post('../feedback/submit_feedback.php', $(this).serialize(), function(res){
        loadFeedback();  // refresh feedback list
        $('#feedbackForm textarea[name="review"]').val(''); // clear textarea
    });
});

loadFeedback(); // initial load
</script>
</body>
</html>
