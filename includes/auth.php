<?php
/**
 * Authentication Helper
 * TRANSLOG AGENCY MANAGER
 */
session_start();

/**
 * Check if user is logged in
 */
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /Gestion_agence_transport/login.php");
        exit();
    }
}

/**
 * Check if user has a specific role or higher
 * Roles: admin > gestionnaire > agent > chauffeur
 */
function check_role($required_role) {
    if (!isset($_SESSION['user_role'])) {
        return false;
    }
    
    $roles = [
        'admin' => 4,
        'gestionnaire' => 3,
        'agent' => 2,
        'chauffeur' => 1
    ];
    
    $user_level = $roles[$_SESSION['user_role']] ?? 0;
    $required_level = $roles[$required_role] ?? 0;
    
    return $user_level >= $required_level;
}

/**
 * Redirect if not authorized
 */
function authorize($required_role) {
    check_login();
    if (!check_role($required_role)) {
        $_SESSION['error'] = "Access denied. You do not have permission to view this page.";
        header("Location: /Gestion_agence_transport/dashboard.php");
        exit();
    }
}

/**
 * Get current user info
 */
function get_user() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'name' => $_SESSION['user_name'] ?? 'Guest',
        'role' => $_SESSION['user_role'] ?? '',
        'email' => $_SESSION['user_email'] ?? ''
    ];
}
?>
