<?php
session_start();
include_once(__DIR__ . "/../dashboard/header.php");
require_once(__DIR__ . "/../db.php"); // DB connection

// --- Get filters ---
$query = trim($_GET['q'] ?? '');
$typeFilter = $_GET['type'] ?? '';
$districtFilter = $_GET['district'] ?? '';
$upzillaFilter = $_GET['upzilla'] ?? '';
$investorFilter = $_GET['investor'] ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

$likeQuery = "%$query%";
$results = [];

// --- Fetch dropdown data ---
$locations = $conn->query("SELECT Location_ID, District_Name, Upzilla FROM location ORDER BY District_Name, Upzilla ASC")->fetch_all(MYSQLI_ASSOC);
$investors = $conn->query("SELECT investor_ID, I_Name FROM investor ORDER BY I_Name ASC")->fetch_all(MYSQLI_ASSOC);

// --- Helper: date filter ---
function getDateFilter($table, $startDate, $endDate){
    switch($table){
        case 'project': $startCol='P_Start_Date'; $endCol='P_Publish_Date'; break;
        case 'research': $startCol='R_Start_Date'; $endCol='R_Publish_Date'; break;
        case 'milestone': $startCol='M_Start_Date'; $endCol='M_Target_Date'; break;
        case 'innovation': $startCol='I_Publish_Date'; $endCol='I_Publish_Date'; break;
        case 'news': $startCol='N_Publish_Date'; $endCol='N_Publish_Date'; break;
        default: return '';
    }
    $filter = '';
    if($startDate && $endDate) $filter = " AND ($startCol BETWEEN '$startDate' AND '$endDate')";
    elseif($startDate) $filter = " AND $startCol >= '$startDate'";
    elseif($endDate) $filter = " AND $endCol <= '$endDate'";
    return $filter;
}

// --- PROJECT ---
if($typeFilter=='' || $typeFilter=='Project'){
    $sql = "SELECT DISTINCT project.Project_ID AS id, P_Title AS title, P_Description AS description, 'Project' AS type
            FROM project
            LEFT JOIN project_occurs_at_location pol ON project.Project_ID = pol.Project_ID
            LEFT JOIN location l ON pol.Location_ID = l.Location_ID
            LEFT JOIN project_funded_by_investor pfi ON project.Project_ID = pfi.Project_ID
            WHERE 1=1";

    if($query) $sql .= " AND (P_Title LIKE ? OR P_Description LIKE ?)";
    if($districtFilter) $sql .= " AND l.District_Name = '".$conn->real_escape_string($districtFilter)."'";
    if($upzillaFilter) $sql .= " AND l.Upzilla = '".$conn->real_escape_string($upzillaFilter)."'";
    if($investorFilter) $sql .= " AND pfi.Investor_ID = ".intval($investorFilter);
    $sql .= getDateFilter('project', $startDate, $endDate);

    $stmt = $conn->prepare($sql);
    if($query) $stmt->bind_param("ss", $likeQuery, $likeQuery);
    $stmt->execute();
    $res = $stmt->get_result();
    while($row = $res->fetch_assoc()) $results[] = $row;
}

// --- RESEARCH ---
if($typeFilter=='' || $typeFilter=='Research'){
    $sql = "SELECT DISTINCT research.Research_ID AS id, R_Title AS title, R_Description AS description, 'Research' AS type
            FROM research
            LEFT JOIN research_occurs_at_location rol ON research.Research_ID = rol.Research_ID
            LEFT JOIN location l ON rol.Location_ID = l.Location_ID
            LEFT JOIN research_funded_by_investor rfi ON research.Research_ID = rfi.Research_ID
            WHERE 1=1";

    if($query) $sql .= " AND (R_Title LIKE ? OR R_Description LIKE ?)";
    if($districtFilter) $sql .= " AND l.District_Name = '".$conn->real_escape_string($districtFilter)."'";
    if($upzillaFilter) $sql .= " AND l.Upzilla = '".$conn->real_escape_string($upzillaFilter)."'";
    if($investorFilter) $sql .= " AND rfi.Investor_ID = ".intval($investorFilter);
    $sql .= getDateFilter('research', $startDate, $endDate);

    $stmt = $conn->prepare($sql);
    if($query) $stmt->bind_param("ss", $likeQuery, $likeQuery);
    $stmt->execute();
    $res = $stmt->get_result();
    while($row = $res->fetch_assoc()) $results[] = $row;
}

// --- MILESTONE ---
if($typeFilter=='' || $typeFilter=='Milestone'){
    $sql = "SELECT Milestone_ID AS id, M_Title AS title, M_Description AS description, 'Milestone' AS type
            FROM milestone WHERE 1=1";
    if($query) $sql .= " AND (M_Title LIKE ? OR M_Description LIKE ?)";
    $sql .= getDateFilter('milestone', $startDate, $endDate);
    $stmt = $conn->prepare($sql);
    if($query) $stmt->bind_param("ss", $likeQuery, $likeQuery);
    $stmt->execute();
    $res = $stmt->get_result();
    while($row = $res->fetch_assoc()) $results[] = $row;
}

// --- INNOVATION ---
if($typeFilter=='' || $typeFilter=='Innovation'){
    $sql = "SELECT Innovation_ID AS id, I_Title AS title, I_Description AS description, 'Innovation' AS type
            FROM innovation WHERE 1=1";
    if($query) $sql .= " AND (I_Title LIKE ? OR I_Description LIKE ?)";
    $sql .= getDateFilter('innovation', $startDate, $endDate);
    $stmt = $conn->prepare($sql);
    if($query) $stmt->bind_param("ss", $likeQuery, $likeQuery);
    $stmt->execute();
    $res = $stmt->get_result();
    while($row = $res->fetch_assoc()) $results[] = $row;
}

// --- NEWS ---
if($typeFilter=='' || $typeFilter=='News'){
    $sql = "SELECT News_ID AS id, N_Title AS title, N_Content AS description, 'News' AS type
            FROM news WHERE 1=1";
    if($query) $sql .= " AND (N_Title LIKE ? OR N_Content LIKE ?)";
    $sql .= getDateFilter('news', $startDate, $endDate);
    $stmt = $conn->prepare($sql);
    if($query) $stmt->bind_param("ss", $likeQuery, $likeQuery);
    $stmt->execute();
    $res = $stmt->get_result();
    while($row = $res->fetch_assoc()) $results[] = $row;
}

// --- DISPLAY RESULTS ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Search Results</title>
<link rel="stylesheet" href="search.css">
</head>
<body>

<div class="search-header">
    <h2>Search Results</h2>
</div>

<form method="GET" action="search.php" class="search-filters">
    <input type="text" name="q" placeholder="Search..." value="<?php echo htmlspecialchars($query); ?>">

    <select name="type">
        <option value="">All Types</option>
        <?php foreach(['Project','Research','Milestone','Innovation','News'] as $type){
            $selected = ($typeFilter==$type)?'selected':''; echo "<option value=\"$type\" $selected>$type</option>";
        }?>
    </select>

   <select name="district" id="district">
    <option value="">All Districts</option>
    <?php
    $districts = array_unique(array_column($locations,'District_Name'));
    sort($districts);
    foreach($districts as $d){ 
        $s = ($districtFilter==$d)?'selected':''; 
        echo "<option value=\"$d\" $s>$d</option>"; 
    }
    ?>
</select>

<select name="upzilla" id="upzilla">
    <option value="">All Upzillas</option>
    <?php
    $upzillas = array_unique(array_column($locations,'Upzilla'));
    sort($upzillas);
    foreach($upzillas as $u){ 
        $s = ($upzillaFilter==$u)?'selected':''; 
        echo "<option value=\"$u\" $s>$u</option>"; 
    }
    ?>
</select>


    <select name="investor">
        <option value="">All Investors</option>
        <?php foreach($investors as $inv){ $s=($investorFilter==$inv['investor_ID'])?'selected':''; echo "<option value=\"{$inv['investor_ID']}\" $s>{$inv['I_Name']}</option>"; } ?>
    </select>

    <label>Start Date:</label>
    <input type="date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>">
    <label>End Date:</label>
    <input type="date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>">

    <button type="submit">Search</button>
</form>

<?php if(count($results)>0): 
    $grouped=[]; foreach($results as $r) $grouped[$r['type']][]=$r; ?>
    <?php foreach($grouped as $type=>$items): ?>
        <div class="result-box">
            <h3 class="result-box-title"><?php echo htmlspecialchars($type); ?></h3>
            <ul class="search-results">
            <?php $count=1; foreach($items as $r): ?>
                <li>
                    <a href="/bddt/<?php echo strtolower($r['type']); ?>/view.php?id=<?php echo $r['id']; ?>">
                        <?php echo $count++; ?>. <?php echo htmlspecialchars($r['title']); ?>
                    </a>
                    
                </li>
            <?php endforeach; ?>
            </ul>
        </div>
    <?php endforeach; ?>
<?php else: ?>
<p>No results found.</p>
<?php endif; ?>

<script src="search.js" defer></script>
<script>
const locations = <?php echo json_encode($locations); ?>;
const districtSelect = document.getElementById('district');
const upzillaSelect = document.getElementById('upzilla');

districtSelect.addEventListener('change', function() {
    const selectedDistrict = this.value;
    
    // Clear current Upzilla options
    upzillaSelect.innerHTML = '<option value="">All Upzillas</option>';
    
    // Filter Upzillas by selected district
    const filtered = locations.filter(loc => loc.District_Name === selectedDistrict);
    
    filtered.forEach(loc => {
        const option = document.createElement('option');
        option.value = loc.Upzilla;
        option.textContent = loc.Upzilla;
        upzillaSelect.appendChild(option);
    });
});
</script>

</body>
</html>
