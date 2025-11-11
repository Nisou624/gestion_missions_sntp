<?php
// Test des chemins d'images
$basePath = dirname(__DIR__);
$basePath = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $basePath);
define('PDF_IMAGE_PATH', $basePath . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR);

echo "<h2>Diagnostic des images</h2>";
echo "<p><strong>Chemin de base :</strong> " . $basePath . "</p>";
echo "<p><strong>Chemin images :</strong> " . PDF_IMAGE_PATH . "</p>";
echo "<hr>";

$images = ['en-tete.jpg', 'ISO.jpg', 'logo-sntp.jpg'];

foreach ($images as $img) {
    $path = PDF_IMAGE_PATH . $img;
    echo "<div style='margin: 15px 0; padding: 10px; border: 1px solid #ccc;'>";
    echo "<h3>$img</h3>";
    echo "<p><strong>Chemin complet :</strong><br>" . $path . "</p>";
    
    if (file_exists($path)) {
        echo "<p style='color: green;'><strong>✓ Fichier existe</strong></p>";
        
        if (is_readable($path)) {
            echo "<p style='color: green;'><strong>✓ Fichier lisible</strong></p>";
            
            $size = @getimagesize($path);
            if ($size) {
                echo "<p><strong>Dimensions :</strong> {$size[0]} x {$size[1]} pixels</p>";
                echo "<p><strong>Type MIME :</strong> {$size['mime']}</p>";
                echo "<p><strong>Taille fichier :</strong> " . round(filesize($path) / 1024, 2) . " KB</p>";
                
                // Afficher l'image
                $imageData = base64_encode(file_get_contents($path));
                echo "<p><img src='data:{$size['mime']};base64,{$imageData}' style='max-width: 300px; border: 1px solid #ddd;'></p>";
            }
        } else {
            echo "<p style='color: red;'><strong>✗ Fichier non lisible (vérifier les permissions)</strong></p>";
        }
    } else {
        echo "<p style='color: red;'><strong>✗ Fichier n'existe pas</strong></p>";
    }
    
    echo "</div>";
}

// Vérifier les permissions du dossier
echo "<hr>";
echo "<h3>Permissions du dossier images</h3>";
$imagesDir = PDF_IMAGE_PATH;
if (is_dir($imagesDir)) {
    echo "<p style='color: green;'>✓ Dossier existe</p>";
    echo "<p>Permissions : " . substr(sprintf('%o', fileperms($imagesDir)), -4) . "</p>";
} else {
    echo "<p style='color: red;'>✗ Dossier n'existe pas</p>";
}
?>

