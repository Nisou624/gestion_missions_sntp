<?php
session_start();
error_reporting(0);
include('../includes/config.php');

header('Content-Type: application/json');

if (strlen($_SESSION['GMScid']) == 0) {
    echo json_encode(['success' => false, 'message' => 'Session expirée']);
    exit();
}

$chef_id = $_SESSION['GMScid'];

if(isset($_POST['signature_data']) && !empty($_POST['signature_data'])) {
    $signature_data = $_POST['signature_data'];
    
    // Décoder l'image base64
    $image_data = str_replace('data:image/png;base64,', '', $signature_data);
    $image_data = str_replace(' ', '+', $image_data);
    $decoded_image = base64_decode($image_data);
    
    $upload_dir = '../assets/signatures/';
    if(!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $filename = 'signature_' . $chef_id . '_' . time() . '.png';
    $file_path = $upload_dir . $filename;
    
    if(file_put_contents($file_path, $decoded_image)) {
        echo json_encode([
            'success' => true, 
            'message' => 'Signature enregistrée avec succès',
            'filename' => $filename
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Données de signature manquantes']);
}
?>

