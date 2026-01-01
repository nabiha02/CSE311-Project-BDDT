<?php
session_start();
require_once(__DIR__ . "/../dashboard/header.php");
require_once("../db.php"); // DB connection only

// ---------------- Check logged-in user ----------------
// Adjust this line to match your actual session variable for user ID
$user_id = $_SESSION['user_id'] ?? $_SESSION['User_ID'] ?? 0;

if ($user_id <= 0) {
    die("<h2 style='color:red;'>Please log in to view your feedbacks.</h2>");
}

// ---------------- Optional debug ----------------
// Uncomment this if you want to see session contents
// echo "<pre>"; print_r($_SESSION); echo "</pre>";

// ---------------- Fetch all feedbacks for this user ----------------
$stmt = $conn->prepare(
    "SELECT f.Feedback_ID, f.Review, f.F_Type, f.Submitted_Date,
            fr.category, fr.entity_name,fr.entity_id
     FROM feedback f
     LEFT JOIN feedback_relation fr ON f.Feedback_ID = fr.feedback_id
     WHERE f.User_ID = ?
     ORDER BY f.Submitted_Date DESC"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Feedbacks</title>
<style>
body {
    padding-top: 120px; /* adjust this to match your header height */
}

/* Optional: container styling */
.container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 20px;
}

header h1 { margin: 0; padding-bottom: 40px; padding-left: 10px; font-size: 28px; letter-spacing: 0.5px; }
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
    font-size: 16px;
    color: #333;
}

.feedback-card .meta {
    font-size: 13px;
    color: #555;
    margin-bottom: 10px;
}

/* Status badge */
.feedback-card .status {
    font-weight: bold;
    padding: 2px 8px;
    border-radius: 4px;
    color: #fff;
    font-size: 12px;
}

.feedback-card .status.pending { background: #ff9800; } /* orange */
.feedback-card .status.approved { background: #4caf50; } /* green */
.feedback-card .status.rejected { background: #f44336; } /* red */

.feedback-card p {
    font-size: 14px;
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
</head>
<body>
<div class="container">
<h1>My Feedbacks</h1>

<?php if ($result->num_rows === 0): ?>
    <p class="no-feedback">You have not submitted any feedback yet.</p>
<?php else: ?>
    <?php while ($row = $result->fetch_assoc()): ?>

        <?php
            // Get category and feedback ID
            $cat = strtolower(trim($row['category'] ?? 'feedback'));
            $id  = $row['entity_id'];

            // Determine link based on category
            switch ($cat) {
                case "project":
                    $link = "/bddt/project/view.php?id=" . $id;
                    break;
                case "research":
                    $link = "/bddt/research/view.php?id=" . $id;
                    break;
                case "innovation":
                    $link = "/bddt/innovation/view.php?id=" . $id;
                    break;
                case "milestone":
                    $link = "/bddt/milestone/view.php?id=" . $id;
                    break;
                default:
                    $link = "#"; // fallback
            }
        ?>

        <div class="feedback-card">
            <a href="<?= $link ?>" style="text-decoration: none; color: inherit;">
                <h3><?= htmlspecialchars($row['entity_name'] ?? 'General') ?> (<?= htmlspecialchars($row['category'] ?? 'General') ?>)</h3>
                <div class="meta">
                    Type: <?= htmlspecialchars($row['F_Type'] ?? 'General') ?> |
                    Submitted: <?= $row['Submitted_Date'] ?>
                </div>
                <p><?= htmlspecialchars($row['Review']) ?></p>
            </a>
        </div>

    <?php endwhile; ?>
<?php endif; ?>


</div>
</body>
</html>
