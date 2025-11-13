<?php
session_start();
require_once('../includes/config.php');
require_once('../includes/security.php');

$securityManager = new SecurityManager($dbh);

// Logger la déconnexion
if(isset($_SESSION['GMSaid']) && isset($_SESSION['login'])) {
    ActivityLogger::log($dbh, $_SESSION['GMSaid'], $_SESSION['login'], 'logout', 'user', $_SESSION['GMSaid'], 
        'Déconnexion administrateur', 'success');
    
    // Détruire la session sécurisée
    $securityManager->destroySecureSession();
}

session_destroy();
header('Location: ../index.php');
exit();
?>

