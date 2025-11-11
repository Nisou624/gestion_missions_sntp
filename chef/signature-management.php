<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['GMScid']) == 0) {
    header('location:logout.php');
    exit();
} else {
    $chef_id = $_SESSION['GMScid'];
    
    // Traitement de l'upload d'image scannée (Méthode 2)
    if(isset($_POST['upload_signature'])) {
        if(isset($_FILES['signature_file']) && $_FILES['signature_file']['error'] == 0) {
            $allowed_types = array('image/png', 'image/jpeg', 'image/jpg');
            $file_type = $_FILES['signature_file']['type'];
            
            if(in_array($file_type, $allowed_types)) {
                $upload_dir = '../assets/signatures/';
                
                // Créer le dossier s'il n'existe pas
                if(!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_extension = pathinfo($_FILES['signature_file']['name'], PATHINFO_EXTENSION);
                $new_filename = 'signature_' . $chef_id . '_uploaded.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if(move_uploaded_file($_FILES['signature_file']['tmp_name'], $upload_path)) {
                    // Mettre à jour la base de données
                    $sql = "UPDATE tblusers SET SignatureImage=:signature, SignatureType='uploaded', SignatureDate=NOW() WHERE ID=:uid";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':signature', $new_filename, PDO::PARAM_STR);
                    $query->bindParam(':uid', $chef_id, PDO::PARAM_STR);
                    $query->execute();
                    
                    echo "<script>alert('Signature uploadée avec succès !'); window.location.href='signature-management.php';</script>";
                } else {
                    echo "<script>alert('Erreur lors du téléchargement du fichier.');</script>";
                }
            } else {
                echo "<script>alert('Type de fichier non autorisé. Utilisez PNG ou JPEG.');</script>";
            }
        }
    }
    
    // Traitement de la signature dessinée (Méthode 1)
    if(isset($_POST['save_drawn_signature'])) {
        $signature_data = $_POST['signature_data'];
        
        if(!empty($signature_data)) {
            // Décoder l'image base64
            $image_data = str_replace('data:image/png;base64,', '', $signature_data);
            $image_data = str_replace(' ', '+', $image_data);
            $decoded_image = base64_decode($image_data);
            
            $upload_dir = '../assets/signatures/';
            if(!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $filename = 'signature_' . $chef_id . '_drawn.png';
            $file_path = $upload_dir . $filename;
            
            if(file_put_contents($file_path, $decoded_image)) {
                // Mettre à jour la base de données
                $sql = "UPDATE tblusers SET SignatureImage=:signature, SignatureType='drawn', SignatureDate=NOW() WHERE ID=:uid";
                $query = $dbh->prepare($sql);
                $query->bindParam(':signature', $filename, PDO::PARAM_STR);
                $query->bindParam(':uid', $chef_id, PDO::PARAM_STR);
                $query->execute();
                
                echo "<script>alert('Signature dessinée enregistrée avec succès !'); window.location.href='signature-management.php';</script>";
            } else {
                echo "<script>alert('Erreur lors de l\\'enregistrement de la signature.');</script>";
            }
        }
    }
    
    // Supprimer la signature
    if(isset($_GET['delete']) && $_GET['delete'] == 1) {
        $sql = "SELECT SignatureImage FROM tblusers WHERE ID=:uid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':uid', $chef_id, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_OBJ);
        
        if($result && $result->SignatureImage) {
            $file_path = '../assets/signatures/' . $result->SignatureImage;
            if(file_exists($file_path)) {
                unlink($file_path);
            }
            
            $sql = "UPDATE tblusers SET SignatureImage=NULL, SignatureType='drawn', SignatureDate=NULL WHERE ID=:uid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':uid', $chef_id, PDO::PARAM_STR);
            $query->execute();
            
            echo "<script>alert('Signature supprimée avec succès !'); window.location.href='signature-management.php';</script>";
        }
    }
    
    // Récupérer la signature actuelle
    $sql = "SELECT SignatureImage, SignatureType, SignatureDate FROM tblusers WHERE ID=:uid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':uid', $chef_id, PDO::PARAM_STR);
    $query->execute();
    $user_signature = $query->fetch(PDO::FETCH_OBJ);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de la Signature - Système de Gestion des Missions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <style>
        .signature-canvas {
            border: 2px solid #007bff;
            border-radius: 8px;
            background-color: #ffffff;
            cursor: crosshair;
            touch-action: none;
        }
        
        .signature-preview {
            border: 2px dashed #6c757d;
            border-radius: 8px;
            padding: 20px;
            background-color: #f8f9fa;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .signature-preview img {
            max-width: 100%;
            max-height: 180px;
        }
        
        .method-card {
            transition: all 0.3s;
        }
        
        .method-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .canvas-container {
            position: relative;
            background: white;
            border-radius: 8px;
            padding: 10px;
        }
        
        .signature-tools {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>
    <?php include_once('includes/sidebar.php'); ?>
    
    <div class="main-content">
        <?php include_once('includes/header.php'); ?>
        
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-signature"></i> Gestion de la Signature Électronique</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Accueil</a></li>
                        <li class="breadcrumb-item active">Gestion Signature</li>
                    </ol>
                </nav>
            </div>
            
            <!-- Signature actuelle -->
            <?php if($user_signature && $user_signature->SignatureImage) { ?>
            <div class="alert alert-success">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5><i class="fas fa-check-circle"></i> Signature Active</h5>
                        <p class="mb-0">
                            <strong>Type:</strong> <?php echo ($user_signature->SignatureType == 'drawn') ? 'Signature Dessinée' : 'Image Scannée'; ?><br>
                            <strong>Date d'enregistrement:</strong> <?php echo date('d/m/Y H:i', strtotime($user_signature->SignatureDate)); ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-right">
                        <img src="../assets/signatures/<?php echo htmlentities($user_signature->SignatureImage); ?>" 
                             alt="Signature" style="max-height: 80px; border: 1px solid #ddd; padding: 5px; background: white;">
                        <br>
                        <a href="?delete=1" class="btn btn-danger btn-sm mt-2" 
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer votre signature ?')">
                            <i class="fas fa-trash"></i> Supprimer
                        </a>
                    </div>
                </div>
            </div>
            <?php } else { ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> 
                Aucune signature enregistrée. Veuillez créer votre signature en utilisant l'une des méthodes ci-dessous.
            </div>
            <?php } ?>
            
            <div class="row">
                <!-- MÉTHODE 1: Signature Dessinée -->
                <div class="col-lg-6 mb-4">
                    <div class="card method-card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-pen"></i> Méthode 1: Signer avec la Souris/Tablette
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">
                                Dessinez votre signature directement dans le cadre ci-dessous avec votre souris 
                                ou votre tablette graphique/tactile.
                            </p>
                            
                            <div class="canvas-container">
                                <canvas id="signature-pad" class="signature-canvas" width="500" height="200"></canvas>
                                <div class="signature-tools">
                                    <div>
                                        <label class="mr-2">Épaisseur:</label>
                                        <input type="range" id="stroke-width" min="1" max="5" value="2" style="width: 100px;">
                                        <span id="stroke-value">2</span>px
                                    </div>
                                    <button type="button" class="btn btn-warning btn-sm" id="clear-signature">
                                        <i class="fas fa-eraser"></i> Effacer
                                    </button>
                                </div>
                            </div>
                            
                            <form method="POST" id="drawn-signature-form" class="mt-3">
                                <input type="hidden" name="signature_data" id="signature-data">
                                <button type="button" class="btn btn-success btn-block btn-lg" id="save-drawn-btn">
                                    <i class="fas fa-save"></i> Enregistrer cette Signature
                                </button>
                            </form>
                            
                            <div class="alert alert-info mt-3">
                                <small>
                                    <i class="fas fa-lightbulb"></i> 
                                    <strong>Astuce:</strong> Pour un meilleur résultat, signez lentement et de manière fluide.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- MÉTHODE 2: Upload d'image scannée -->
                <div class="col-lg-6 mb-4">
                    <div class="card method-card h-100">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-upload"></i> Méthode 2: Télécharger une Image Scannée
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">
                                Téléchargez une image scannée de votre signature manuscrite avec le cachet officiel.
                            </p>
                            
                            <div class="signature-preview mb-3" id="preview-container">
                                <span class="text-muted">
                                    <i class="fas fa-image fa-3x"></i><br>
                                    Aperçu de l'image
                                </span>
                            </div>
                            
                            <form method="POST" enctype="multipart/form-data" id="upload-signature-form">
                                <div class="form-group">
                                    <label for="signature_file">
                                        <i class="fas fa-file-image"></i> Sélectionner une Image:
                                    </label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="signature_file" 
                                               name="signature_file" accept="image/png,image/jpeg,image/jpg" required>
                                        <label class="custom-file-label" for="signature_file">Choisir un fichier...</label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Formats acceptés: PNG, JPG, JPEG. Taille maximale: 2 MB.
                                    </small>
                                </div>
                                
                                <button type="submit" name="upload_signature" class="btn btn-success btn-block btn-lg">
                                    <i class="fas fa-cloud-upload-alt"></i> Télécharger et Enregistrer
                                </button>
                            </form>
                            
                            <div class="alert alert-info mt-3">
                                <small>
                                    <i class="fas fa-lightbulb"></i> 
                                    <strong>Recommandation:</strong> Utilisez une image de haute qualité avec un fond blanc 
                                    pour un meilleur rendu sur les PDF.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Comparaison des méthodes -->
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-balance-scale"></i> Comparaison des Deux Méthodes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th width="25%">Critère</th>
                                    <th width="37.5%">Méthode 1: Signature Dessinée</th>
                                    <th width="37.5%">Méthode 2: Image Scannée</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Facilité d'utilisation</strong></td>
                                    <td><span class="badge badge-success">Très facile</span> - Immédiat, pas de préparation</td>
                                    <td><span class="badge badge-warning">Nécessite préparation</span> - Scanner/photographier la signature</td>
                                </tr>
                                <tr>
                                    <td><strong>Qualité visuelle</strong></td>
                                    <td><span class="badge badge-info">Bonne</span> - Dépend de l'habileté avec la souris</td>
                                    <td><span class="badge badge-success">Excellente</span> - Signature manuscrite authentique</td>
                                </tr>
                                <tr>
                                    <td><strong>Cachet officiel</strong></td>
                                    <td><span class="badge badge-danger">Non inclus</span> - Signature uniquement</td>
                                    <td><span class="badge badge-success">Inclus possible</span> - Peut contenir le cachet</td>
                                </tr>
                                <tr>
                                    <td><strong>Rapidité</strong></td>
                                    <td><span class="badge badge-success">Instantané</span> - 30 secondes</td>
                                    <td><span class="badge badge-warning">Plus long</span> - 2-5 minutes</td>
                                </tr>
                                <tr>
                                    <td><strong>Équipement requis</strong></td>
                                    <td><span class="badge badge-success">Aucun</span> - Souris ou écran tactile</td>
                                    <td><span class="badge badge-warning">Scanner</span> - ou appareil photo</td>
                                </tr>
                                <tr>
                                    <td><strong>Recommandation</strong></td>
                                    <td>Idéale pour tests et développement</td>
                                    <td><strong>Recommandée pour production</strong> avec cachet officiel</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // ===== MÉTHODE 1: Canvas pour signature dessinée =====
        const canvas = document.getElementById('signature-pad');
        const ctx = canvas.getContext('2d');
        const strokeWidthInput = document.getElementById('stroke-width');
        const strokeValueDisplay = document.getElementById('stroke-value');
        
        let isDrawing = false;
        let lastX = 0;
        let lastY = 0;
        let strokeWidth = 2;
        
        // Configurer le canvas
        ctx.strokeStyle = '#000000';
        ctx.lineJoin = 'round';
        ctx.lineCap = 'round';
        ctx.lineWidth = strokeWidth;
        
        // Mise à jour de l'épaisseur
        strokeWidthInput.addEventListener('input', function() {
            strokeWidth = this.value;
            strokeValueDisplay.textContent = this.value;
            ctx.lineWidth = strokeWidth;
        });
        
        // Fonctions de dessin - Souris
        function startDrawing(e) {
            isDrawing = true;
            const rect = canvas.getBoundingClientRect();
            [lastX, lastY] = [e.clientX - rect.left, e.clientY - rect.top];
        }
        
        function draw(e) {
            if (!isDrawing) return;
            
            const rect = canvas.getBoundingClientRect();
            const currentX = e.clientX - rect.left;
            const currentY = e.clientY - rect.top;
            
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(currentX, currentY);
            ctx.stroke();
            
            [lastX, lastY] = [currentX, currentY];
        }
        
        function stopDrawing() {
            isDrawing = false;
        }
        
        // Événements souris
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);
        
        // Fonctions de dessin - Tactile
        function startDrawingTouch(e) {
            e.preventDefault();
            isDrawing = true;
            const rect = canvas.getBoundingClientRect();
            const touch = e.touches[0];
            [lastX, lastY] = [touch.clientX - rect.left, touch.clientY - rect.top];
        }
        
        function drawTouch(e) {
            e.preventDefault();
            if (!isDrawing) return;
            
            const rect = canvas.getBoundingClientRect();
            const touch = e.touches[0];
            const currentX = touch.clientX - rect.left;
            const currentY = touch.clientY - rect.top;
            
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(currentX, currentY);
            ctx.stroke();
            
            [lastX, lastY] = [currentX, currentY];
        }
        
        function stopDrawingTouch(e) {
            e.preventDefault();
            isDrawing = false;
        }
        
        // Événements tactiles
        canvas.addEventListener('touchstart', startDrawingTouch);
        canvas.addEventListener('touchmove', drawTouch);
        canvas.addEventListener('touchend', stopDrawingTouch);
        canvas.addEventListener('touchcancel', stopDrawingTouch);
        
        // Effacer la signature
        document.getElementById('clear-signature').addEventListener('click', function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        });
        
        // Enregistrer la signature dessinée
        document.getElementById('save-drawn-btn').addEventListener('click', function() {
            // Vérifier si le canvas est vide
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const pixels = imageData.data;
            let isEmpty = true;
            
            for(let i = 0; i < pixels.length; i += 4) {
                if(pixels[i + 3] !== 0) {
                    isEmpty = false;
                    break;
                }
            }
            
            if(isEmpty) {
                alert('Veuillez dessiner votre signature avant de l\'enregistrer !');
                return;
            }
            
            // Convertir le canvas en base64
            const signatureData = canvas.toDataURL('image/png');
            document.getElementById('signature-data').value = signatureData;
            
            // Confirmation
            if(confirm('Êtes-vous sûr de vouloir enregistrer cette signature ?')) {
                document.getElementById('drawn-signature-form').submit();
            }
        });
        
        // ===== MÉTHODE 2: Aperçu de l'image uploadée =====
        document.getElementById('signature_file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if(file) {
                // Vérifier la taille (2 MB max)
                if(file.size > 2 * 1024 * 1024) {
                    alert('Le fichier est trop volumineux ! Taille maximale: 2 MB');
                    this.value = '';
                    return;
                }
                
                // Afficher le nom du fichier
                $('.custom-file-label').text(file.name);
                
                // Prévisualisation
                const reader = new FileReader();
                reader.onload = function(event) {
                    const previewContainer = document.getElementById('preview-container');
                    previewContainer.innerHTML = '<img src="' + event.target.result + '" alt="Aperçu signature">';
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Confirmation avant upload
        document.getElementById('upload-signature-form').addEventListener('submit', function(e) {
            if(!document.getElementById('signature_file').files.length) {
                alert('Veuillez sélectionner un fichier !');
                e.preventDefault();
                return false;
            }
            
            return confirm('Êtes-vous sûr de vouloir enregistrer cette image comme signature ?');
        });
    });
    </script>
</body>
</html>

<?php } ?>

