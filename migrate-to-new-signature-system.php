<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('includes/config.php');

// Sécurité: accessible seulement par admin
if (!isset($_SESSION['GMSaid'])) {
    die('Accès réservé aux administrateurs');
}

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <title>Migration Système de Signature</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
        h2 { color: #007bff; margin-top: 0; }
        code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-top: 10px; }
    </style>
</head>
<body>
    <h1>🔄 Migration vers le Nouveau Système de Signature</h1>";

echo "<div class='section'>
    <h2>Étape 1: Modification de la Structure BDD</h2>";

try {
    // Supprimer anciennes colonnes
    $sql = "ALTER TABLE tblusers DROP COLUMN IF EXISTS SignatureImage";
    $dbh->exec($sql);
    echo "<p class='success'>✓ Colonne SignatureImage supprimée</p>";
    
    $sql = "ALTER TABLE tblusers DROP COLUMN IF EXISTS SignatureType";
    $dbh->exec($sql);
    echo "<p class='success'>✓ Colonne SignatureType supprimée</p>";
    
    $sql = "ALTER TABLE tblusers DROP COLUMN IF EXISTS SignatureDate";
    $dbh->exec($sql);
    echo "<p class='success'>✓ Colonne SignatureDate supprimée</p>";
    
    // Ajouter nouvelles colonnes
    $sql = "ALTER TABLE tblusers ADD COLUMN IF NOT EXISTS OfficialStamp VARCHAR(255) DEFAULT NULL";
    $dbh->exec($sql);
    echo "<p class='success'>✓ Colonne OfficialStamp ajoutée</p>";
    
    $sql = "ALTER TABLE tblusers ADD COLUMN IF NOT EXISTS StampUploadDate DATETIME DEFAULT NULL";
    $dbh->exec($sql);
    echo "<p class='success'>✓ Colonne StampUploadDate ajoutée</p>";
    
    // Vérifier SignaturePath dans missions
    $sql = "ALTER TABLE tblmissions ADD COLUMN IF NOT EXISTS SignaturePath VARCHAR(255) DEFAULT NULL";
    $dbh->exec($sql);
    echo "<p class='success'>✓ Colonne SignaturePath vérifiée dans tblmissions</p>";
    
} catch(Exception $e) {
    echo "<p class='error'>✗ Erreur: " . $e->getMessage() . "</p>";
}

echo "</div>";

echo "<div class='section'>
    <h2>Étape 2: Création des Dossiers</h2>";

// Créer dossier stamps
$stamps_dir = 'assets/stamps/';
if(!is_dir($stamps_dir)) {
    if(mkdir($stamps_dir, 0755, true)) {
        echo "<p class='success'>✓ Dossier créé: $stamps_dir</p>";
    } else {
        echo "<p class='error'>✗ Impossible de créer: $stamps_dir</p>";
    }
} else {
    echo "<p class='success'>✓ Dossier existe déjà: $stamps_dir</p>";
}

// Vérifier permissions
if(is_writable($stamps_dir)) {
    echo "<p class='success'>✓ Dossier accessible en écriture</p>";
} else {
    echo "<p class='warning'>⚠ Dossier non accessible en écriture. Exécutez: <code>chmod 755 $stamps_dir</code></p>";
}

// Dossier signatures (doit déjà exister)
$signatures_dir = 'assets/signatures/';
if(!is_dir($signatures_dir)) {
    mkdir($signatures_dir, 0755, true);
}
echo "<p class='success'>✓ Dossier signatures vérifié: $signatures_dir</p>";

echo "</div>";

echo "<div class='section'>
    <h2>Étape 3: Migration des Anciennes Signatures</h2>";

// Vérifier s'il existe des anciennes signatures à migrer
$old_signatures = glob('assets/signatures/signature_*_uploaded.*');
$old_drawn = glob('assets/signatures/signature_*_drawn.*');

$all_old = array_merge($old_signatures, $old_drawn);

if(count($all_old) > 0) {
    echo "<p class='warning'>⚠ " . count($all_old) . " ancienne(s) signature(s) trouvée(s)</p>";
    echo "<p>Ces fichiers peuvent être déplacés vers le dossier stamps si vous souhaitez les réutiliser comme cachets:</p>";
    echo "<ul>";
    foreach($all_old as $file) {
        $filename = basename($file);
        echo "<li>$filename</li>";
    }
    echo "</ul>";
    echo "<p class='warning'>Note: Ces fichiers seront conservés mais non utilisés par le nouveau système.</p>";
} else {
    echo "<p class='success'>✓ Aucune ancienne signature à migrer</p>";
}

echo "</div>";

echo "<div class='section'>
    <h2>Étape 4: Vérification des Chefs de Département</h2>";

$sql = "SELECT ID, Nom, Prenom, Email, OfficialStamp FROM tblusers WHERE UserType='chef'";
$query = $dbh->prepare($sql);
$query->execute();
$chefs = $query->fetchAll(PDO::FETCH_OBJ);

if(count($chefs) > 0) {
    echo "<p>Nombre de chefs trouvés: " . count($chefs) . "</p>";
    echo "<table border='1' cellpadding='10' style='width: 100%; border-collapse: collapse;'>";
    echo "<tr style='background: #f8f9fa;'><th>ID</th><th>Nom</th><th>Email</th><th>Cachet</th><th>Action</th></tr>";
    
    foreach($chefs as $chef) {
        echo "<tr>";
        echo "<td>" . htmlentities($chef->ID) . "</td>";
        echo "<td>" . htmlentities($chef->Nom . ' ' . $chef->Prenom) . "</td>";
        echo "<td>" . htmlentities($chef->Email) . "</td>";
        
        if($chef->OfficialStamp) {
            echo "<td class='success'>✓ Configuré</td>";
            echo "<td>-</td>";
        } else {
            echo "<td class='warning'>⚠ Non configuré</td>";
            echo "<td><small>Doit aller dans Gestion Cachet</small></td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='warning'>⚠ Aucun chef de département trouvé</p>";
}

echo "</div>";

echo "<div class='section'>
    <h2>✅ Migration Terminée</h2>
    <p><strong>Prochaines étapes pour les chefs de département:</strong></p>
    <ol>
        <li>Se connecter à leur compte</li>
        <li>Accéder à <strong>Gestion du Cachet Officiel</strong></li>
        <li>Télécharger leur cachet officiel (image scannée avec signature)</li>
        <li>Valider des missions en signant manuellement à chaque fois</li>
    </ol>
    
    <p><strong>Fichiers créés/modifiés:</strong></p>
    <ul>
        <li><code>chef/stamp-management.php</code> - Nouvelle page de gestion du cachet</li>
        <li><code>chef/validate-mission.php</code> - Mise à jour avec signature manuelle</li>
        <li><code>includes/generate-pdf.php</code> - Génération PDF avec cachet + signature</li>
        <li><code>assets/stamps/</code> - Nouveau dossier pour les cachets</li>
    </ul>
    
    <a href='chef/stamp-management.php' class='btn'>📝 Aller à la Gestion du Cachet</a>
    <a href='chef/pending-missions.php' class='btn' style='background: #28a745;'>📋 Voir les Missions</a>
</div>";

echo "</body></html>";
?>

