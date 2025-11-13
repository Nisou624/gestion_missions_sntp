<?php
session_start();
require_once('../includes/config.php');
require_once('../includes/security.php');

$securityManager = new SecurityManager($dbh);

if(isset($_SESSION['GMSuid']) && isset($_SESSION['login'])) {
    ActivityLogger::log($dbh, $_SESSION['GMSuid'], $_SESSION['login'], 'logout', 'user', $_SESSION['GMSuid'], 
        'Déconnexion utilisateur', 'success');
    
    $securityManager->destroySecureSession();
}

session_destroy();
header('Location: ../index.php');
exit();
?>

