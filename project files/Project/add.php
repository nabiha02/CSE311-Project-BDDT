<?php
session_start();
require_once("../db.php");

function getDistrictName($conn, $district_id) {
    $stmt = $conn->prepare(
        "SELECT District_Name FROM district WHERE District_ID = ?"
    );
    $stmt->bind_param("i", $district_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    return $row ? $row['District_Name'] : null;
}

// Access control
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ["Admin", "Govt Employee"])) {
    die("Access Denied");
}

// Safe output helper
function h($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

// Fetch dropdown data
$sectors   = $conn->query("SELECT Sector_ID, S_Name FROM sector ORDER BY S_Name");
$districts = $conn->query("SELECT District_ID, District_Name FROM district ORDER BY District_Name ASC");

// Form Submit
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title  = trim($_POST['P_Title']);
    $desc   = $_POST['P_Description'];
    $pdate  = $_POST['P_Start_Date'];
    $budget = $_POST['P_Budget'] ?: NULL;
    $cost   = $_POST['P_Cost'] ?: NULL;
    $status = $_POST['P_Status'];
    $date   = $_POST['P_Publish_Date'];
    $sector = $_POST['Sector_ID'];

    // Check for duplicate project title
    $check = $conn->prepare("SELECT Project_ID FROM project WHERE P_Title=? LIMIT 1");
    $check->bind_param("s", $title);
    $check->execute();
    $res = $check->get_result();
    if($res->num_rows > 0){
        die("Project with this title already exists.");
    }

    // Insert Project
    $sql = $conn->prepare("INSERT INTO project (P_Title, P_Description, P_Start_Date, P_Budget, P_Cost, P_Status, P_Publish_Date, Sector_ID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $sql->bind_param("ssssssss", $title, $desc, $pdate, $budget, $cost, $status, $date, $sector);
    $sql->execute();
    $project_id = $sql->insert_id;

    // Handle Multiple Investors
    if(!empty($_POST['I_Name'])){
        foreach($_POST['I_Name'] as $idx => $iname){
            $iname = trim($iname);
            if($iname === '') continue;

            $itype = $_POST['I_Type'][$idx] ?? NULL;
            $amount = $_POST['P_Contribution_Amount'][$idx] ?: NULL;
            $agreement = $_POST['P_Agreement_Date'][$idx] ?: NULL;

            // Check existing investor
            $check_inv = $conn->prepare("SELECT investor_ID FROM investor WHERE I_Name=? LIMIT 1");
            $check_inv->bind_param("s", $iname);
            $check_inv->execute();
            $res_inv = $check_inv->get_result();
            $inv_id = ($res_inv->num_rows > 0) ? $res_inv->fetch_assoc()['investor_ID'] : null;

            if(!$inv_id){
                $ins_inv = $conn->prepare("INSERT INTO investor (I_Name, I_Type) VALUES (?, ?)");
                $ins_inv->bind_param("ss", $iname, $itype);
                $ins_inv->execute();
                $inv_id = $ins_inv->insert_id;
            }

            // Link investor to project (insert even if amount/agreement blank)
            $q2 = $conn->prepare("INSERT INTO project_funded_by_investor (Project_ID, Investor_ID, P_Contribution_Amount, P_Agreement_Date) VALUES (?, ?, ?, ?)");
            $q2->bind_param("iiss", $project_id, $inv_id, $amount, $agreement);
            $q2->execute();
        }
    }

    // Handle Multiple Locations
    if(!empty($_POST['Upzilla'])){
        foreach($_POST['Upzilla'] as $idx => $upzilla){
            $upzilla = trim($upzilla);
            $district_id = $_POST['District_ID'][$idx] ?? NULL;
            if($upzilla === '' || !$district_id) continue;

            // Check if Upzilla exists in district
            $check_loc = $conn->prepare("SELECT Location_ID FROM location WHERE Upzilla=? AND District_ID=? LIMIT 1");
            $check_loc->bind_param("si", $upzilla, $district_id);
            $check_loc->execute();
            $res_loc = $check_loc->get_result();
            $loc_id = ($res_loc->num_rows > 0) ? $res_loc->fetch_assoc()['Location_ID'] : null;

           if(!$loc_id){

    // 🔹 Get District_Name manually (trigger replacement)
    $district_name = getDistrictName($conn, $district_id);
    if(!$district_name){
        die("Invalid District selected.");
    }

    $ins_loc = $conn->prepare(
        "INSERT INTO location (Upzilla, District_ID, District_Name)
         VALUES (?, ?, ?)"
    );
    $ins_loc->bind_param("sis", $upzilla, $district_id, $district_name);
    $ins_loc->execute();
    $loc_id = $ins_loc->insert_id;
}


            // Link project to location
            $q3 = $conn->prepare("INSERT INTO project_occurs_at_location (Project_ID, Location_ID) VALUES (?, ?)");
            $q3->bind_param("ii", $project_id, $loc_id);
            $q3->execute();
        }
    }

    header("Location: index.php");
    exit;
}

include("../dashboard/header.php");
?>

<style>
.form-wrapper { display: flex; justify-content: center; padding-top: 50px; }
.form-box { width: 750px; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); font-family: Inter, sans-serif; }
.form-box h2 { text-align: center; margin-bottom: 20px; }
.form-box label { font-weight: 600; margin-top: 12px; display: block; }
.form-box input, .form-box select, .form-box textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; margin-top: 5px; }
.form-box button { padding: 10px 15px; font-size: 13px; border: none; border-radius: 5px; cursor: pointer; }
#add-investor, #add-location { background: #555; color: white; margin-top: 8px; }
#add-investor:hover, #add-location:hover { background: #333; }
.remove-investor, .remove-location { background: #c00; color: white; font-size: 12px; margin-top: 5px; }
.remove-investor:hover, .remove-location:hover { background: #900; }
.investor-entry, .location-entry { border: 1px solid #eee; padding: 10px; margin-bottom: 10px; border-radius: 6px; position: relative; }
</style>

<div class="form-wrapper">
    <div class="form-box">
        <h2>Add New Project</h2>
        <form method="POST">

            <label>Project Title</label>
            <input type="text" name="P_Title" required>

            <label>Description</label>
            <textarea name="P_Description" rows="4"></textarea>

            <label>Start Date</label>
            <input type="date" name="P_Start_Date" required>

            <label>Budget</label>
            <input type="text" name="P_Budget">

            <label>Cost</label>
            <input type="text" name="P_Cost">

            <label>Publish Date</label>
            <input type="date" name="P_Publish_Date" required>

            <label>Sector</label>
            <select name="Sector_ID" required>
                <option value="">Select Sector</option>
                <?php while($s=$sectors->fetch_assoc()): ?>
                    <option value="<?= $s['Sector_ID'] ?>"><?= h($s['S_Name']) ?></option>
                <?php endwhile; ?>
            </select>

            <hr>
            <h3>Funding Information (Optional)</h3>
            <div id="investors-wrapper">
                <div class="investor-entry">
                    <label>Investor Name</label>
                    <input type="text" name="I_Name[]" placeholder="Enter investor name">

                    <label>Investor Type</label>
                    <select name="I_Type[]">
                        <option value="Individual">Individual</option>
                        <option value="Institutional">Institutional</option>
                        <option value="Government">Government</option>
                        <option value="Corporate">Corporate</option>
                        <option value="Venture Capital">Venture Capital</option>
                        <option value="Private Equity">Private Equity</option>
                        <option value="Crowdfunding">Crowdfunding</option>
                    </select>

                    <label>Contribution Amount</label>
                    <input type="text" name="P_Contribution_Amount[]" placeholder="Amount">

                    <label>Agreement Date</label>
                    <input type="date" name="P_Agreement_Date[]">

                    <button type="button" class="remove-investor">Remove</button>
                </div>
            </div>
            <button type="button" id="add-investor">Add Another Investor</button>

            <hr>
            <h3>Location Information</h3>
            <div id="locations-wrapper">
                <div class="location-entry">
                    <label>Upzilla (Sub-district)</label>
                    <input type="text" name="Upzilla[]" placeholder="Enter Upzilla">

                    <label>District</label>
                    <select name="District_ID[]">
                        <option value="">Select District</option>
                        <?php while($d = $districts->fetch_assoc()): ?>
                            <option value="<?= $d['District_ID'] ?>"><?= h($d['District_Name']) ?></option>
                        <?php endwhile; ?>
                    </select>

                    <button type="button" class="remove-location">Remove</button>
                </div>
            </div>
            <button type="button" id="add-location">Add Another Location</button>

            <hr>
            <label>Project Status:</label><br>
            <select name="P_Status" required>
                <option value="On Track">On Track</option>
                <option value="Active">Active</option>
                <option value="Completed">Completed</option>
                <option value="Cancelled">Cancelled</option>
            </select>
            <br><br>

            <button type="submit">Submit Project</button>
        </form>
    </div>
</div>

<script>
// Investors
document.getElementById('add-investor').addEventListener('click', () => {
    const wrapper = document.getElementById('investors-wrapper');
    const entry = wrapper.querySelector('.investor-entry').cloneNode(true);
    entry.querySelectorAll('input').forEach(i => i.value = '');
    entry.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
    wrapper.appendChild(entry);
});
document.addEventListener('click', e => {
    if(e.target.classList.contains('remove-investor')){
        const wrapper = document.getElementById('investors-wrapper');
        if(wrapper.querySelectorAll('.investor-entry').length > 1){
            e.target.closest('.investor-entry').remove();
        }
    }
});

// Locations
document.getElementById('add-location').addEventListener('click', () => {
    const wrapper = document.getElementById('locations-wrapper');
    const entry = wrapper.querySelector('.location-entry').cloneNode(true);
    entry.querySelector('input').value = '';
    entry.querySelector('select').selectedIndex = 0;
    wrapper.appendChild(entry);
});
document.addEventListener('click', e => {
    if(e.target.classList.contains('remove-location')){
        const wrapper = document.getElementById('locations-wrapper');
        if(wrapper.querySelectorAll('.location-entry').length > 1){
            e.target.closest('.location-entry').remove();
        }
    }
});
</script>
