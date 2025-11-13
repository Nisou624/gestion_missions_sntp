<?php
session_start();
require_once('../includes/config.php');
require_once('../includes/security.php');

$securityManager = new SecurityManager($dbh);

if(isset($_SESSION['GMScid']) && isset($_SESSION['login'])) {
    ActivityLogger::log($dbh, $_SESSION['GMScid'], $_SESSION['login'], 'logout', 'user', $_SESSION['GMScid'], 
        'Déconnexion chef de département', 'success');
    
    $securityManager->destroySecureSession();
}

session_destroy();
header('Location: ../index.php');
exit();
?>

