<?php
session_start();

function isUserLoggedIn() {
    return isset($_SESSION['user_id']); // Adjust this condition based on your login implementation

}

function getUserRole() {
    // You should have a way to retrieve the user role from your database or session
    // For demonstration purposes, let's assume there is a 'role' key in the session
    return isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';
}

function redirectIfNotLoggedIn() {
    if (!isUserLoggedIn()) {
        header('Location: login.php'); // Redirect to the login page if not logged in
        exit();
    }
}

function redirectIfNotAllowed($allowedRoles) {
    $userRole = getUserRole();
    if (!in_array($userRole, $allowedRoles)) {
        header('Location: unauthorized.php'); // Redirect to an unauthorized page
        exit();
    }
}

// Add any other common functions or configurations related to login here
// Remove the closing PHP tag
?>