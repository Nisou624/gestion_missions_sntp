<?php
// Configuration de la base de données pour l'interface chef
define('DB_HOST','localhost');
define('DB_USER','root');
define('DB_PASS','');
define('DB_NAME','gestion_missions_db');

// Connexion à la base de données
try {
    $dbh = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8", DB_USER, DB_PASS, array(
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ));
} catch (PDOException $e) {
    exit("Erreur de connexion: " . $e->getMessage());
}
?>
