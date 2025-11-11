<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gestion_missions_db');

// Configuration générale
define('SITE_NAME', 'Système de Gestion des Ordres de Mission - SNTP');
define('SITE_URL', 'http://localhost/gestion-missions/');
define('UPLOAD_PATH', 'assets/uploads/');

// Configuration de l'organisation
define('ORG_NAME', 'Société Nationale de Travaux Publics');
define('ORG_SHORT', 'SNTP');
define('ORG_ADDRESS', 'Route Nationale n°5 El Hamiz BP 39 - Bordj El Kiffan - Alger');
define('ORG_TEL', '023.86.35.95/99');
define('ORG_FAX', '023.86.36.03');
define('ORG_WEBSITE', 'www.sntp.dz');

// Connexion à la base de données
try {
    $dbh = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8", DB_USER, DB_PASS, array(
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ));
} catch (PDOException $e) {
    exit("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Fonctions utilitaires
function generateReferenceNumber() {
    global $dbh;
    $year = date('Y');
    
    // Récupérer le dernier numéro de référence de l'année
    $sql = "SELECT ReferenceNumber FROM tblmissions WHERE ReferenceNumber LIKE :year ORDER BY ID DESC LIMIT 1";
    $query = $dbh->prepare($sql);
    $query->bindParam(':year', $year.'-%.', PDO::PARAM_STR);
    $query->execute();
    
    if ($query->rowCount() > 0) {
        $result = $query->fetch(PDO::FETCH_OBJ);
        $lastRef = $result->ReferenceNumber;
        $number = intval(substr($lastRef, 5)) + 1;
    } else {
        $number = 1;
    }
    
    return $year . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function checkUserSession($role = null) {
    session_start();
    
    $sessionKey = '';
    switch($role) {
        case 'admin':
            $sessionKey = 'GMSaid';
            break;
        case 'chef':
            $sessionKey = 'GMScid';
            break;
        case 'user':
            $sessionKey = 'GMSuid';
            break;
    }
    
    if (!isset($_SESSION[$sessionKey]) || strlen($_SESSION[$sessionKey]) == 0) {
        return false;
    }
    return true;
}

// Statuts des missions
$statuts_missions = array(
    'en_attente' => array('label' => 'En attente', 'class' => 'warning'),
    'validee' => array('label' => 'Validée', 'class' => 'success'),
    'rejetee' => array('label' => 'Rejetée', 'class' => 'danger'),
    'en_cours' => array('label' => 'En cours', 'class' => 'info')
);

// Motifs de déplacement
$motifs_mission = array(
    'Formation professionnelle',
    'Audit et inspection',
    'Réunion de coordination',
    'Mission technique',
    'Supervision de chantier',
    'Représentation officielle',
    'Expertise technique',
    'Autre'
);

// Moyens de transport
$moyens_transport = array(
    'Véhicule de service',
    'Transport en commun',
    'Avion',
    'Train',
    'Véhicule personnel'
);
?>
