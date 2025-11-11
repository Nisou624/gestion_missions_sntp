<?php
// Script de test pour vérifier les deux méthodes de signature
echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <title>Test des Signatures</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .test-section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
    </style>
</head>
<body>
    <h1>🧪 Test des Fonctionnalités de Signature</h1>";

// Test 1: Vérification du dossier signatures
echo "<div class='test-section'>
    <h2>Test 1: Dossier de Stockage</h2>";

$signature_dir = 'assets/signatures/';
if(!is_dir($signature_dir)) {
    mkdir($signature_dir, 0755, true);
    echo "<p class='success'>✓ Dossier créé: $signature_dir</p>";
} else {
    echo "<p class='success'>✓ Dossier existe: $signature_dir</p>";
}

if(is_writable($signature_dir)) {
    echo "<p class='success'>✓ Dossier accessible en écriture</p>";
} else {
    echo "<p class='error'>✗ ERREUR: Dossier non accessible en écriture</p>";
}
echo "</div>";

// Test 2: Bibliothèques requises
echo "<div class='test-section'>
    <h2>Test 2: Bibliothèques PHP</h2>";

if(function_exists('base64_decode')) {
    echo "<p class='success'>✓ base64_decode disponible</p>";
} else {
    echo "<p class='error'>✗ base64_decode non disponible</p>";
}

if(function_exists('imagecreatefrompng')) {
    echo "<p class='success'>✓ GD Library disponible</p>";
} else {
    echo "<p class='error'>✗ GD Library non disponible (nécessaire pour manipulation d'images)</p>";
}
echo "</div>";

// Test 3: TCPDF
echo "<div class='test-section'>
    <h2>Test 3: TCPDF</h2>";

if(file_exists('vendor/tecnickcom/tcpdf/tcpdf.php')) {
    echo "<p class='success'>✓ TCPDF installé</p>";
    require_once('vendor/tecnickcom/tcpdf/tcpdf.php');
    
    try {
        $pdf = new TCPDF();
        echo "<p class='success'>✓ TCPDF peut être instancié</p>";
    } catch(Exception $e) {
        echo "<p class='error'>✗ Erreur TCPDF: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p class='error'>✗ TCPDF non trouvé. Exécutez: composer require tecnickcom/tcpdf</p>";
}
echo "</div>";

// Test 4: Permissions MySQL
echo "<div class='test-section'>
    <h2>Test 4: Base de Données</h2>";

try {
    include('includes/config.php');
    echo "<p class='success'>✓ Connexion à la base de données réussie</p>";
    
    // Vérifier si les colonnes existent
    $sql = "SHOW COLUMNS FROM tblusers LIKE 'SignatureImage'";
    $query = $dbh->prepare($sql);
    $query->execute();
    
    if($query->rowCount() > 0) {
        echo "<p class='success'>✓ Colonne SignatureImage existe</p>";
    } else {
        echo "<p class='error'>✗ Colonne SignatureImage manquante. Exécutez le script SQL fourni.</p>";
    }
    
} catch(Exception $e) {
    echo "<p class='error'>✗ Erreur base de données: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Résumé
echo "<div class='test-section'>
    <h2>📊 Résumé</h2>
    <p><strong>Méthode 1 (Signature Dessinée):</strong> Utilise Canvas HTML5 + JavaScript</p>
    <p><strong>Méthode 2 (Image Scannée):</strong> Upload de fichier PNG/JPG</p>
    <hr>
    <p><strong>Recommandation:</strong></p>
    <ul>
        <li>Pour le développement et tests: Utilisez la Méthode 1 (plus rapide)</li>
        <li>Pour la production: Utilisez la Méthode 2 avec cachet officiel scanné</li>
    </ul>
</div>";

echo "</body></html>";
?>

