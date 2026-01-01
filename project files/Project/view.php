<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . "/../dashboard/header.php");
require_once(__DIR__ . "/../db.php");


function h($value) {
    return htmlspecialchars($value ?? "Not Enough Data", ENT_QUOTES, 'UTF-8');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Project ID");
}

$pid = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM project WHERE Project_ID = ?");
$stmt->bind_param("i", $pid);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();
$stmt->close();

$start_display = ($project['P_Start_Date'] === "0000-00-00" || !$project['P_Start_Date']) ? "Not Enough Data" : $project['P_Start_Date'];
$publish_display = ($project['P_Publish_Date'] === "0000-00-00" || !$project['P_Publish_Date']) ? "Not Enough Data" : $project['P_Publish_Date'];

$inv_stmt = $conn->prepare("
    SELECT inv.I_Name, inv.I_Type, pfi.P_Contribution_Amount, pfi.P_Agreement_Date
    FROM project_funded_by_investor pfi
    JOIN investor inv ON inv.investor_ID = pfi.Investor_ID
    WHERE pfi.Project_Id = ?
");
$inv_stmt->bind_param("i", $pid);
$inv_stmt->execute();
$investors = $inv_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$inv_stmt->close();

$loc_stmt = $conn->prepare("
    SELECT l.Upzilla, d.District_Name
    FROM project_occurs_at_location pol
    JOIN location l ON l.Location_ID = pol.Location_Id
    JOIN district d ON d.District_ID = l.District_ID
    WHERE pol.Project_Id = ?
");
$loc_stmt->bind_param("i", $pid);
$loc_stmt->execute();
$locations = $loc_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$loc_stmt->close();

/* ================= PROJECT DOCUMENTS ================= */
$doc_stmt = $conn->prepare("
    SELECT d.Document_ID, d.File_Name, d.D_Uploaded_At
    FROM document d
    JOIN document_relation r ON r.document_id = d.Document_ID
    WHERE r.category = 'Project'
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
    WHERE nr.category = 'Project'
      AND nr.entity_id = ?
    ORDER BY n.N_Publish_Date DESC
");

if (!$new_stmt) {
    die("News query failed: " . $conn->error);
}

$new_stmt->bind_param("i", $pid);
$new_stmt->execute();
$news = $new_stmt->get_result();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= h($project['P_Title']) ?></title>
    <link rel="stylesheet" href="index.css">

    <style>
        /* Push content below fixed header */
        .page-content {
            margin-top: 120px; /* adjust to match header height */
        }
    </style>
</head>

<body>

<div class="page-content">

<a href="index.php" class="back">&larr; Back to Projects</a>

<h1 class="detail-title"><?= h($project['P_Title']) ?></h1>

<div class="detail-container">

    <div class="detail-left">

        <div class="info-card">
            <div class="info-header">
                <span class="info-icon">📄</span>
                <h2>Project Overview</h2>
            </div>

            <div class="info-grid">
                <div><strong>Status:</strong> <?= h($project['P_Status']) ?></div>
                <div><strong>Start Date:</strong> <?= h($start_display) ?></div>
                <div><strong>Publish Date:</strong> <?= h($publish_display) ?></div>
                <div><strong>Budget:</strong> <?= h($project['P_Budget']) ?></div>
                <div><strong>Cost:</strong> <?= h($project['P_Cost']) ?></div>
            </div>

            <h3>Description</h3>
            <div class="description-box">
                <?= nl2br(h($project['P_Description'])) ?>
            </div>
        </div>
        

        <div class="info-card mt-4">
    <div class="info-header">
        <span class="info-icon">📄</span>
        <h2>Project Documents</h2>
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
        <p class="empty-text">No documents uploaded for this project.</p>
    <?php endif; ?>

    <a href="http://localhost/bddt/document/upload.php"
       class="btn btn-sm btn-primary mt-2">
        Upload Document
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
        <input type="hidden" name="category" value="Project">
        <input type="hidden" name="entity_id" value="<?= (int)$pid ?>">

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
    WHERE fr.category = 'Project'
      AND fr.entity_id = ?
      AND f.F_Status = 'published'
    ORDER BY f.Submitted_Date DESC
");
$fb_stmt->bind_param("i", $pid);
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
                        <p><strong><?= h($i['I_Name']) ?></strong>
                        <span class="muted">(<?= h($i['I_Type']) ?>)</span></p>
                        <p><b>Contribution:</b> <?= h($i['P_Contribution_Amount']) ?></p>
                        <p><b>Agreement:</b> <?= h($i['P_Agreement_Date']) ?></p>
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
                        <p><strong><?= h($l['Upzilla']) ?></strong></p>
                        <p class="muted"><?= h($l['District_Name']) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="info-card mt-4">
    <div class="info-header">
        <span class="info-icon">🗞️</span>
        <h2>Project News</h2>
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
        <p class="empty-text">No news uploaded for this project.</p>
    <?php endif; ?>

    <a href="http://localhost/bddt/news/add.php"
       class="btn btn-sm btn-primary mt-2">
        ➡️Upload a News
    </a>
</div>


    </div>

</div>

</div> <!-- END page-content -->

<?php $doc_stmt->close(); ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function loadFeedback() {
    $.get('../feedback/fetch_feedback.php', {
        category: 'Project',
        entity_id: <?= $pid ?>
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
