<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('includes/config.php');

// Vérifier l'authentification
if (!isset($_SESSION['GMScid'])) {
    die('Veuillez vous connecter en tant que chef de département');
}

$mid = isset($_GET['mid']) ? intval($_GET['mid']) : 0;

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <title>Diagnostic Signature</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #007bff; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .info { color: #17a2b8; }
        h2 { color: #333; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table td, table th { padding: 8px; border: 1px solid #ddd; text-align: left; }
        table th { background: #f8f9fa; font-weight: bold; }
        img { max-width: 300px; border: 2px solid #ddd; padding: 5px; background: white; }
    </style>
</head>
<body>
    <h1>🔍 Diagnostic de Signature - Mission #$mid</h1>";

// Test 1: Vérifier la structure de la base de données
echo "<div class='section'>
    <h2>1. Structure de la Base de Données</h2>";

try {
    // Vérifier colonne SignatureImage dans tblusers
    $sql = "SHOW COLUMNS FROM tblusers LIKE 'SignatureImage'";
    $query = $dbh->prepare($sql);
    $query->execute();
    
    if($query->rowCount() > 0) {
        echo "<p class='success'>✓ Colonne SignatureImage existe dans tblusers</p>";
    } else {
        echo "<p class='error'>✗ Colonne SignatureImage MANQUANTE dans tblusers</p>";
        echo "<p>Exécutez: <code>ALTER TABLE tblusers ADD COLUMN SignatureImage VARCHAR(255) DEFAULT NULL;</code></p>";
    }
    
    // Vérifier colonne SignaturePath dans tblmissions
    $sql = "SHOW COLUMNS FROM tblmissions LIKE 'SignaturePath'";
    $query = $dbh->prepare($sql);
    $query->execute();
    
    if($query->rowCount() > 0) {
        echo "<p class='success'>✓ Colonne SignaturePath existe dans tblmissions</p>";
    } else {
        echo "<p class='error'>✗ Colonne SignaturePath MANQUANTE dans tblmissions</p>";
        echo "<p>Exécutez: <code>ALTER TABLE tblmissions ADD COLUMN SignaturePath VARCHAR(255) DEFAULT NULL;</code></p>";
    }
    
} catch(Exception $e) {
    echo "<p class='error'>Erreur: " . $e->getMessage() . "</p>";
}

echo "</div>";

// Test 2: Vérifier le dossier signatures
echo "<div class='section'>
    <h2>2. Dossier de Stockage</h2>";

$signature_dir = 'assets/signatures/';

if(!is_dir($signature_dir)) {
    echo "<p class='error'>✗ Dossier n'existe pas: $signature_dir</p>";
    if(mkdir($signature_dir, 0755, true)) {
        echo "<p class='success'>✓ Dossier créé avec succès</p>";
    } else {
        echo "<p class='error'>✗ Impossible de créer le dossier</p>";
    }
} else {
    echo "<p class='success'>✓ Dossier existe: $signature_dir</p>";
}

if(is_writable($signature_dir)) {
    echo "<p class='success'>✓ Dossier accessible en écriture</p>";
} else {
    echo "<p class='error'>✗ Dossier NON accessible en écriture</p>";
    echo "<p>Exécutez: <code>chmod 755 $signature_dir</code></p>";
}

// Lister les fichiers de signature
$files = glob($signature_dir . '*.{png,jpg,jpeg}', GLOB_BRACE);
echo "<p class='info'>Nombre de fichiers de signature: " . count($files) . "</p>";

if(count($files) > 0) {
    echo "<table>";
    echo "<tr><th>Fichier</th><th>Taille</th><th>Permissions</th><th>Aperçu</th></tr>";
    foreach($files as $file) {
        $filename = basename($file);
        $size = filesize($file);
        $perms = substr(sprintf('%o', fileperms($file)), -4);
        
        echo "<tr>";
        echo "<td>$filename</td>";
        echo "<td>" . number_format($size / 1024, 2) . " KB</td>";
        echo "<td>$perms</td>";
        echo "<td><img src='$file' alt='$filename'></td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "</div>";

// Test 3: Données du chef connecté
echo "<div class='section'>
    <h2>3. Signature du Chef Connecté</h2>";

$chef_id = $_SESSION['GMScid'];
$sql = "SELECT ID, Nom, Prenom, Email, SignatureImage, SignatureType, SignatureDate FROM tblusers WHERE ID=:uid";
$query = $dbh->prepare($sql);
$query->bindParam(':uid', $chef_id, PDO::PARAM_STR);
$query->execute();
$chef = $query->fetch(PDO::FETCH_OBJ);

if($chef) {
    echo "<table>";
    echo "<tr><th>Champ</th><th>Valeur</th></tr>";
    echo "<tr><td>ID</td><td>" . htmlentities($chef->ID) . "</td></tr>";
    echo "<tr><td>Nom</td><td>" . htmlentities($chef->Nom . ' ' . $chef->Prenom) . "</td></tr>";
    echo "<tr><td>Email</td><td>" . htmlentities($chef->Email) . "</td></tr>";
    echo "<tr><td>SignatureImage</td><td>" . ($chef->SignatureImage ? htmlentities($chef->SignatureImage) : '<span class="error">NULL</span>') . "</td></tr>";
    echo "<tr><td>SignatureType</td><td>" . ($chef->SignatureType ? htmlentities($chef->SignatureType) : '<span class="error">NULL</span>') . "</td></tr>";
    echo "<tr><td>SignatureDate</td><td>" . ($chef->SignatureDate ? htmlentities($chef->SignatureDate) : '<span class="error">NULL</span>') . "</td></tr>";
    echo "</table>";
    
    if($chef->SignatureImage) {
        $sig_path = $signature_dir . $chef->SignatureImage;
        if(file_exists($sig_path)) {
            echo "<p class='success'>✓ Fichier de signature existe</p>";
            echo "<p><img src='$sig_path' alt='Signature'></p>";
        } else {
            echo "<p class='error'>✗ Fichier de signature INTROUVABLE: $sig_path</p>";
        }
    } else {
        echo "<p class='warning'>⚠ Aucune signature enregistrée</p>";
        echo "<p>Allez dans <a href='chef/signature-management.php'>Gestion de la Signature</a> pour créer une signature</p>";
    }
} else {
    echo "<p class='error'>Chef non trouvé dans la base de données</p>";
}

echo "</div>";

// Test 4: Données de la mission (si spécifiée)
if($mid > 0) {
    echo "<div class='section'>
        <h2>4. Données de la Mission #$mid</h2>";
    
    $sql = "SELECT m.*, 
                   u.Nom as UserNom, u.Prenom as UserPrenom,
                   v.Nom as ValidatorNom, v.Prenom as ValidatorPrenom, 
                   v.SignatureImage as ValidatorSignature
            FROM tblmissions m 
            JOIN tblusers u ON m.UserID = u.ID 
            LEFT JOIN tblusers v ON m.ValidatedBy = v.ID
            WHERE m.ID = :mid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':mid', $mid, PDO::PARAM_STR);
    $query->execute();
    $mission = $query->fetch(PDO::FETCH_OBJ);
    
    if($mission) {
        echo "<table>";
        echo "<tr><th>Champ</th><th>Valeur</th></tr>";
        echo "<tr><td>Référence</td><td>" . htmlentities($mission->ReferenceNumber) . "</td></tr>";
        echo "<tr><td>Statut</td><td>" . htmlentities($mission->Status) . "</td></tr>";
        echo "<tr><td>Demandeur</td><td>" . htmlentities($mission->UserNom . ' ' . $mission->UserPrenom) . "</td></tr>";
        echo "<tr><td>Validateur</td><td>" . ($mission->ValidatorNom ? htmlentities($mission->ValidatorNom . ' ' . $mission->ValidatorPrenom) : '<span class="error">Non validée</span>') . "</td></tr>";
        echo "<tr><td>SignaturePath (Mission)</td><td>" . ($mission->SignaturePath ? htmlentities($mission->SignaturePath) : '<span class="error">NULL</span>') . "</td></tr>";
        echo "<tr><td>ValidatorSignature</td><td>" . ($mission->ValidatorSignature ? htmlentities($mission->ValidatorSignature) : '<span class="error">NULL</span>') . "</td></tr>";
        echo "</table>";
        
        // Vérifier quelle signature sera utilisée
        if($mission->SignaturePath) {
            $sig_path = $signature_dir . $mission->SignaturePath;
            echo "<p class='info'>Signature utilisée: SignaturePath de la mission</p>";
            if(file_exists($sig_path)) {
                echo "<p class='success'>✓ Fichier existe: $sig_path</p>";
                echo "<p><img src='$sig_path' alt='Signature'></p>";
            } else {
                echo "<p class='error'>✗ Fichier INTROUVABLE: $sig_path</p>";
            }
        } elseif($mission->ValidatorSignature) {
            $sig_path = $signature_dir . $mission->ValidatorSignature;
            echo "<p class='info'>Signature utilisée: SignatureImage du validateur</p>";
            if(file_exists($sig_path)) {
                echo "<p class='success'>✓ Fichier existe: $sig_path</p>";
                echo "<p><img src='$sig_path' alt='Signature'></p>";
            } else {
                echo "<p class='error'>✗ Fichier INTROUVABLE: $sig_path</p>";
            }
        } else {
            echo "<p class='warning'>⚠ Aucune signature disponible pour cette mission</p>";
        }
        
        // Lien pour générer le PDF
        if($mission->Status == 'validee') {
            echo "<p><a href='includes/generate-pdf.php?mid=$mid' target='_blank' class='btn'>Générer le PDF</a></p>";
        }
        
    } else {
        echo "<p class='error'>Mission non trouvée</p>";
    }
    
    echo "</div>";
}

// Test 5: Recommandations
echo "<div class='section'>
    <h2>5. Recommandations</h2>
    <ol>
        <li>Si aucune signature n'apparaît, allez dans <strong>Gestion de la Signature</strong> et créez une signature</li>
        <li>Après avoir créé une signature, validez une nouvelle mission</li>
        <li>Vérifiez que le fichier de signature existe dans <code>assets/signatures/</code></li>
        <li>Assurez-vous que les permissions du dossier sont correctes (755)</li>
        <li>Utilisez ce diagnostic avec <code>?mid=X</code> pour vérifier une mission spécifique</li>
    </ol>
</div>";

echo "</body></html>";
?>

