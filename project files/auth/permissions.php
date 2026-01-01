<?php
function isAdmin() {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin');
}

function isGovtEmployee() {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'Govt Employee');
}

function isUser() {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'Citizen');
}

// Check if user can insert new data
function canInsert() {
    return isAdmin() || isGovtEmployee();
}

// Check if user can update data
function canUpdate() {
    return isAdmin() || isGovtEmployee();
}

// Check if user can delete data
function canDelete() {
    return isAdmin() || isGovtEmployee();
}
?>
