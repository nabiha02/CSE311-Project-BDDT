<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . "/../dashboard/header.php");
require_once(__DIR__ . "/../db.php");

// Admin check
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== "Admin") {
    die("<h2 style='color:red;'>Access Denied: Admins only.</h2>");
}

// Fetch feedbacks
$stmt = $conn->prepare(
    "SELECT f.Feedback_ID, f.Review, f.F_Type, f.Submitted_Date, f.F_Status, f.User_ID,
            fr.category, fr.entity_name
     FROM feedback f
     JOIN feedback_relation fr ON f.Feedback_ID = fr.feedback_id
     ORDER BY f.Submitted_Date DESC"
);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Feedbacks</title>
<style>
/* Give space for the existing dashboard header */
body {
    padding-top: 120px; /* adjust this to match your header height */
}

/* Optional: container styling */
.container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 20px;
}

header h1 { margin: 0; padding-bottom: 20px; padding-left: 10px; font-size: 28px; letter-spacing: 0.5px; }
/* Feedback cards with colors */
.feedback-card {
    background: #fff;
    border-left: 6px solid #673ab7; /* colored accent */
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.feedback-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.15);
}

.feedback-card h3 {
    margin: 0 0 8px;
    font-size: 20px;
    color: #333;
}

.feedback-card .meta {
    font-size: 14px;
    color: #555;
    margin-bottom: 10px;
}

/* Status badge */
.feedback-card .status {
    font-weight: bold;
    padding: 2px 8px;
    border-radius: 4px;
    color: #fff;
    font-size: 13px;
}

.feedback-card .status.pending { background: #ff9800; } /* orange */
.feedback-card .status.approved { background: #4caf50; } /* green */
.feedback-card .status.rejected { background: #f44336; } /* red */

.feedback-card p {
    font-size: 16px;
    line-height: 1.5;
    white-space: pre-line;
}

/* Buttons */
.feedback-actions a {
    display: inline-block;
    margin-right: 10px;
    text-decoration: none;
    font-weight: 500;
    padding: 6px 12px;
    border-radius: 4px;
    transition: 0.2s;
    color: #fff;
    font-size: 14px;
}

.feedback-actions a.approve { background: #4caf50; }
.feedback-actions a.reject { background: #f44336; }
.feedback-actions a.delete { background: #590202ff; }

.feedback-actions a:hover { opacity: 0.85; }

.no-feedback {
    text-align: center;
    color: #555;
    margin-top: 50px;
    font-size: 18px;
}

</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<header>
    <h1>Admin Dashboard: Feedbacks</h1>
</header>

<div class="container" id="feedbackContainer">
<?php if ($result->num_rows === 0): ?>
    <p class="no-feedback">No feedbacks found.</p>
<?php else: ?>
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="feedback-card" id="feedback-<?= $row['Feedback_ID'] ?>">
            <h3><?= htmlspecialchars($row['entity_name']) ?> (<?= htmlspecialchars($row['category']) ?>)</h3>
            <div class="meta">
                Type: <?= htmlspecialchars($row['F_Type'] ?? 'General') ?> |
                User ID: <?= htmlspecialchars($row['User_ID']) ?> |
                Status: <?= htmlspecialchars($row['F_Status']) ?> |
                Submitted: <?= $row['Submitted_Date'] ?>
            </div>
            <p><?= htmlspecialchars($row['Review']) ?></p>
            <div class="feedback-actions">
                <?php if ($row['F_Status'] === 'pending'): ?>
                    <a class="approve" href="approve_feedback.php?id=<?= $row['Feedback_ID'] ?>">✅ Approve</a>
                    <a class="reject" href="approve_feedback.php?id=<?= $row['Feedback_ID'] ?>&reject=1"
                       onclick="return confirm('Reject this feedback?');">❌ Reject</a>
                <?php endif; ?>
                <a class="delete" data-id="<?= $row['Feedback_ID'] ?>">🗑️ Delete</a>
            </div>
        </div>
    <?php endwhile; ?>
<?php endif; ?>
<?php $stmt->close(); ?>
</div>

<script>
$(document).ready(function(){
    $('#feedbackContainer').on('click', 'a.delete', function(e){
        e.preventDefault();
        const feedbackId = $(this).data('id');
        if(!confirm('Are you sure you want to delete this feedback?')) return;

        $.ajax({
            url: 'delete_feedback.php',
            type: 'POST',
            data: { feedback_id: feedbackId },
            success: function(response) {
                let res = {};
                try { res = JSON.parse(response); } catch(e){ alert('The feedback got deleted'); return; }

                if(res.status === 'success'){
                    $('#feedback-' + feedbackId).fadeOut(300, function(){ $(this).remove(); });
                } else {
                    alert('Error: ' + (res.message || 'Failed to delete'));
                }
            },
            error: function() {
                alert('AJAX error: Could not contact server.');
            }
        });
    });
});
</script>

</body>
</html>
