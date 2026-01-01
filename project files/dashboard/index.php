<?php include "header.php"; ?>
<?php include "../db.php"; ?>

<div class="content">

    <div class="dashboard-header">
        <h1>Bangladesh Development Tracker</h1>
        <p>Tracking Progress • Empowering Bangladesh</p>
    </div>

    <?php 
        // Total counts
        $p = $conn->query("SELECT COUNT(*) AS t FROM Project")->fetch_assoc()['t'];
        $r = $conn->query("SELECT COUNT(*) AS t FROM Research")->fetch_assoc()['t'];
        $i = $conn->query("SELECT COUNT(*) AS t FROM Innovation")->fetch_assoc()['t'];
        $m = $conn->query("SELECT COUNT(*) AS t FROM Milestone")->fetch_assoc()['t'];
        $n = $conn->query("SELECT COUNT(*) AS t FROM News")->fetch_assoc()['t'];

        // Latest 5 items for each type
        $latest_projects = $conn->query("
            SELECT Project_ID, P_Title AS title, COALESCE(P_Publish_Date, P_Start_Date, '0000-00-00') AS dt
            FROM Project
            ORDER BY dt DESC
            LIMIT 5
        ");

        // Latest 5 inserted Projects
$latest_projects = $conn->query("
    SELECT Project_ID, P_Title AS title, COALESCE(P_Publish_Date, P_Start_Date, '0000-00-00') AS dt
    FROM Project
    ORDER BY Project_ID DESC
    LIMIT 5
");

// Latest 5 inserted Research
$latest_research = $conn->query("
    SELECT Research_ID, R_Title AS title, COALESCE(R_Publish_Date, R_Start_Date, '0000-00-00') AS dt
    FROM Research
    ORDER BY Research_ID DESC
    LIMIT 5
");

// Latest 5 inserted Innovations
$latest_innovations = $conn->query("
    SELECT Innovation_ID, I_Title AS title, COALESCE(I_Publish_Date, '0000-00-00') AS dt
    FROM Innovation
    ORDER BY Innovation_ID DESC
    LIMIT 5
");

// Latest 5 inserted Milestones
$latest_milestones = $conn->query("
    SELECT Milestone_ID, M_Title AS title, COALESCE(M_Start_Date, M_Target_Date, '0000-00-00') AS dt
    FROM Milestone
    ORDER BY Milestone_ID DESC
    LIMIT 5
");

// Latest 5 inserted News
$latest_news = $conn->query("
    SELECT News_ID, N_Title AS title, COALESCE(N_Publish_Date, '0000-00-00') AS dt
    FROM News
    ORDER BY News_ID DESC
    LIMIT 5
");

    ?>

    <!-- TOP CARDS -->
    <div class="cards-container fade-in">

        <a href="/bddt/project/index.php" class="card card-blue">
    <i class="icon ri-building-4-line"></i>
    <span>Total Projects</span>
    <h2><?= $p ?></h2>
</a>
        <a href="/bddt/research/index.php" class="card card-green">
    <i class="ri-flask-line icon"></i>
    <span>Total Research</span>
    <h2><?= $r ?></h2>
</a>


      <a href="/bddt/innovation/index.php" class="card card-orange">
    <i class="icon ri-lightbulb-flash-line"></i>
    <span>Total Innovation</span>
    <h2><?= $i ?></h2>
</a>

       <a href="/bddt/milestone/index.php" class="card card-purple">
    <i class="ri-trophy-line icon"></i>
    <span>Total Milestone</span>
    <h2><?= $m ?></h2>
</a>


        <a href="/bddt/news/index.php" class="card card-red">
    <i class="ri-newspaper-line icon"></i>
    <span>Total News</span>
    <h2><?= $n ?></h2>
</a>


    </div>

    <!-- LATEST 5 ITEMS PER TYPE -->
    <!-- LATEST UPDATES GRID -->
<div class="latest-section fade-in" style="margin-top:50px;">
    <h2>Latest Updates</h2>

    <?php 
    $sections = [
        'Projects' => $latest_projects,
        'Research' => $latest_research,
        'Innovations' => $latest_innovations,
        'Milestones' => $latest_milestones,
        'News' => $latest_news
    ];
    ?>

<?php foreach ($sections as $type => $query) : ?>
    <h3><?= $type ?></h3>
    <div class="latest-grid">
        <?php $i = 0; ?>
        <?php while ($row = $query->fetch_assoc()) : 
            // Determine URL and date label
            switch ($type) {
                case 'Projects':
                    $url = "../project/view.php?id=" . $row['Project_ID'];
                    $date_label = "Published Date";
                    break;
                case 'Research':
                    $url = "../research/view.php?id=" . $row['Research_ID'];
                    $date_label = "Published Date";
                    break;
                case 'Innovations':
                    $url = "../innovation/view.php?id=" . $row['Innovation_ID'];
                    $date_label = "Published Date";
                    break;
                case 'Milestones':
                    $url = "../milestone/view.php?id=" . $row['Milestone_ID'];
                    $date_label = "Start Date";
                    break;
                case 'News':
                    $url = "../news/view.php?id=" . $row['News_ID'];
                    $date_label = "Published Date";
                    break;
                default:
                    $url = "#";
                    $date_label = "";
            }

            // Format the date nicely
            $formatted_date = date("M d, Y", strtotime($row['dt']));
       // Determine URL and date label
switch ($type) {
    case 'Projects':
        $url = "../project/view.php?id=" . $row['Project_ID'];
        $date_label = "Published Date";
        break;
    case 'Research':
        $url = "../research/view.php?id=" . $row['Research_ID'];
        $date_label = "Published Date";
        break;
    case 'Innovations':
        $url = "../innovation/view.php?id=" . $row['Innovation_ID'];
        $date_label = "Published Date";
        break;
    case 'Milestones':
        $url = "../milestone/view.php?id=" . $row['Milestone_ID'];
        $date_label = "Start Date";
        break;
    case 'News':
        $url = "../news/view.php?id=" . $row['News_ID'];
        $date_label = "Published Date";
        break;
    default:
        $url = "#";
        $date_label = "";
}

// ICON SWITCH (place this here)
switch ($type) {
    case 'Projects':     $icon = "ri-building-4-line"; break;
    case 'Research':     $icon = "ri-flask-line"; break;
    case 'Innovations':  $icon = "ri-lightbulb-flash-line"; break;
    case 'Milestones':   $icon = "ri-trophy-line"; break;
    case 'News':         $icon = "ri-newspaper-line"; break;
    default:             $icon = "ri-file-line"; 
}

// Format the date
$formatted_date = date("M d, Y", strtotime($row['dt']));
 ?>
        
           <a href="<?= $url ?>" class="latest-card latest-type-<?= strtolower($type); ?>" data-i="<?= $i++; ?>">


    <div class="latest-icon">
        <i class="<?= $icon ?>"></i>
    </div>

    <div class="latest-card-header">
        <div class="latest-date"><strong><?= $date_label ?>:</strong> <?= $formatted_date ?></div>
    </div>

    <div class="latest-card-title"><?= htmlspecialchars($row['title']) ?></div>
</a>

        <?php endwhile; ?>
    </div>
<?php endforeach; ?>


</div>


<script>
document.querySelectorAll('.card h2').forEach(el => {
    let count = parseInt(el.innerText);
    el.innerText = 0;
    let i = 0;
    let step = Math.ceil(count / 50);
    let interval = setInterval(() => {
        i += step;
        if(i >= count) { i = count; clearInterval(interval); }
        el.innerText = i;
    }, 20);
});
</script>


<?php include "footer.php"; ?>
