<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}

function getUserName() {
    return $_SESSION['user_name'] ?? null;
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /ticket_system/login.php");
        exit();
    }
}

function requireRole($role) {
    requireLogin();
    if (getUserRole() !== $role) {
        header("Location: /ticket_system/login.php");
        exit();
    }
}

function redirectByRole() {
    $role = getUserRole();
    switch ($role) {
        case 'admin':
            header("Location: /ticket_system/admin/dashboard.php");
            break;
        case 'staff':
            header("Location: /ticket_system/staff/dashboard.php");
            break;
        case 'customer':
            header("Location: /ticket_system/customer/dashboard.php");
            break;
        default:
            header("Location: /ticket_system/login.php");
            break;
    }
    exit();
}
?>