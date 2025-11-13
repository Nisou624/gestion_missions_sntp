<?php
// Ce fichier doit être inclus au début de chaque page protégée
if (!isset($_SESSION)) {
    session_start();
}

require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/security.php');

$securityManager = new SecurityManager($dbh);

// Vérifier selon le rôle
if (isset($_SESSION['GMSaid'])) {
    // Admin
    if (!$securityManager->validateSession($_SESSION['GMSaid'])) {
        session_destroy();
        header('location: logout.php');
        exit();
    }
} elseif (isset($_SESSION['GMScid'])) {
    // Chef
    if (!$securityManager->validateSession($_SESSION['GMScid'])) {
        session_destroy();
        header('location: logout.php');
        exit();
    }
} elseif (isset($_SESSION['GMSuid'])) {
    // User
    if (!$securityManager->validateSession($_SESSION['GMSuid'])) {
        session_destroy();
        header('location: logout.php');
        exit();
    }
} else {
    // Aucune session
    header('location: logout.php');
    exit();
}

// Nettoyer les anciennes sessions toutes les heures
if (!isset($_SESSION['last_cleanup']) || (time() - $_SESSION['last_cleanup']) > 3600) {
    $securityManager->cleanupOldSessions();
    $_SESSION['last_cleanup'] = time();
}
?>

