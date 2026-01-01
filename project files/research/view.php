<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . "/../dashboard/header.php");
require_once(__DIR__ . "/../db.php");

function h($v) { return htmlspecialchars($v ?? "Not Enough Data", ENT_QUOTES, 'UTF-8'); }

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Research ID");
}

$rid = (int)$_GET['id'];

/* -------------------------------------------------
   MAIN RESEARCH RECORD
------------------------------------------------- */
$sql = "SELECT * FROM research WHERE Research_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $rid);
$stmt->execute();
$research = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$research) die("Research not found.");

$start_display   = ($research['R_Start_Date']   === "0000-00-00" ? "Not Enough Data" : $research['R_Start_Date']);
$publish_display = ($research['R_Publish_Date'] === "0000-00-00" ? "Not Enough Data" : $research['R_Publish_Date']);

/* -------------------------------------------------
   INVESTORS (FIXED COLUMN NAMES)
------------------------------------------------- */
$sql_inv = "
    SELECT inv.I_Name, inv.I_Type, rfi.R_Contribution_Amount, rfi.R_Agreement_Date
    FROM research_funded_by_investor rfi
    JOIN investor inv ON inv.investor_ID = rfi.Investor_ID
    WHERE rfi.Research_ID = ?
";

$stmt_inv = $conn->prepare($sql_inv);
$stmt_inv->bind_param("i", $rid);
$stmt_inv->execute();
$investors = $stmt_inv->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_inv->close();

/* -------------------------------------------------
   LOCATIONS (FIXED COLUMN NAMES)
------------------------------------------------- */
$sql_loc = "
    SELECT l.Upzilla, d.District_Name
    FROM research_occurs_at_location rol
    JOIN location l ON l.Location_ID = rol.Location_ID
    JOIN district d ON d.District_ID = l.District_ID
    WHERE rol.Research_ID = ?
";

$stmt_loc = $conn->prepare($sql_loc);
$stmt_loc->bind_param("i", $rid);
$stmt_loc->execute();
$locations = $stmt_loc->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_loc->close();

/* -------------------------------------------------
   SECTOR
------------------------------------------------- */
$sector_name = "Not Enough Data";
if (!empty($research['Sector_ID'])) {
    $stmt_sec = $conn->prepare("SELECT S_Name FROM sector WHERE Sector_ID = ?");
    $stmt_sec->bind_param("i", $research['Sector_ID']);
    $stmt_sec->execute();
    $res = $stmt_sec->get_result()->fetch_assoc();
    if ($res) $sector_name = $res['S_Name'];
    $stmt_sec->close();
}

/* ================= PROJECT DOCUMENTS ================= */
$doc_stmt = $conn->prepare("
    SELECT d.Document_ID, d.File_Name, d.D_Uploaded_At
    FROM document d
    JOIN document_relation r ON r.document_id = d.Document_ID
    WHERE r.category = 'Research'
      AND r.entity_id = ?
      AND d.Status = 'Approved'
    ORDER BY d.D_Uploaded_At DESC
");
$doc_stmt->bind_param("i", $rid);
$doc_stmt->execute();
$documents = $doc_stmt->get_result();

$new_stmt = $conn->prepare("
    SELECT n.News_ID, n.N_Title, n.N_Publish_Date
    FROM news n
    JOIN news_relation nr ON nr.news_id = n.News_ID
    WHERE nr.category = 'Research'
      AND nr.entity_id = ?
    ORDER BY n.N_Publish_Date DESC
");

if (!$new_stmt) {
    die("News query failed: " . $conn->error);
}

$new_stmt->bind_param("i", $rid);
$new_stmt->execute();
$news = $new_stmt->get_result();


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= h($research['R_Title']) ?></title>
    <link rel="stylesheet" href="index.css">
</head>

<body>

<div class="page-content">

<a href="index.php" class="back">&larr; Back to Research</a>

<h1 class="detail-title"><?= h($research['R_Title']) ?></h1>

<div class="detail-container">

    <div class="detail-left">
        <div class="info-card">
            <div class="info-header">
                <span class="info-icon">🔬</span>
                <h2>Research Overview</h2>
            </div>

            <div class="info-grid">
                <div><strong>Status:</strong> <?= h($research['R_Status']) ?></div>
                <div><strong>Start Date:</strong> <?= h($start_display) ?></div>
                <div><strong>Publish Date:</strong> <?= h($publish_display) ?></div>
                <div><strong>Budget:</strong> <?= h($research['R_Budget']) ?></div>
                <div><strong>Cost:</strong> <?= h($research['R_Cost']) ?></div>
                <div><strong>Sector:</strong> <?= h($sector_name) ?></div>
            </div>

            <h3>Description</h3>
            <div class="description-box">
                <?= nl2br(h($research['R_Description'])) ?>
            </div>
        </div>
        <div class="info-card mt-4">
    
</div>
    </div>

    <div class="detail-right">

        <div class="info-card">
            <div class="info-header">
                <span class="info-icon">💰</span>
                <h2>Investors</h2>
            </div>

            <?php if (empty($investors)): ?>
                <p class="empty-text">No investor information available.</p>
            <?php else: ?>
                <?php foreach ($investors as $i): ?>
                    <div class="list-item">
                        <strong><?= h($i['I_Name']) ?></strong> (<?= h($i['I_Type']) ?>)<br>
                        <b>Contribution:</b> <?= h($i['R_Contribution_Amount']) ?><br>
                        <b>Agreement:</b> <?= h($i['R_Agreement_Date']) ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="info-card">
            <div class="info-header">
                <span class="info-icon">📍</span>
                <h2>Locations</h2>
            </div>

            <?php if (empty($locations)): ?>
                <p class="empty-text">No location information available.</p>
            <?php else: ?>
                <?php foreach ($locations as $l): ?>
                    <div class="list-item">
                        <strong><?= h($l['Upzilla']) ?></strong><br>
                        <span class="muted"><?= h($l['District_Name']) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="info-card mt-4">
    <div class="info-header">
        <span class="info-icon">📄</span>
        <h2>Research Documents</h2>
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
        <p class="empty-text">No documents uploaded for this research.</p>
    <?php endif; ?>

    <a href="http://localhost/bddt/document/upload.php"
       class="btn btn-sm btn-primary mt-2">
        Upload Document
    </a>
    </div>
    
<div class="info-card mt-4">
    <div class="info-header">
        <span class="info-icon">🗞️</span>
        <h2>Research News</h2>
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
        <p class="empty-text">No news uploaded for this research.</p>
    <?php endif; ?>

    <a href="http://localhost/bddt/news/add.php"
       class="btn btn-sm btn-primary mt-2">
        ➡️Upload a News
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
        <input type="hidden" name="category" value="Research">
        <input type="hidden" name="entity_id" value="<?= (int)$rid ?>">

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
    WHERE fr.category = 'Research'
      AND fr.entity_id = ?
      AND f.F_Status = 'published'
    ORDER BY f.Submitted_Date DESC
");
$fb_stmt->bind_param("i", $rid);
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

</div>
<?php $doc_stmt->close(); ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function loadFeedback() {
    $.get('../feedback/fetch_feedback.php', {
        category: 'Research',
        entity_id: <?= $rid ?>
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
