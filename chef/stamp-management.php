<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['GMScid']) == 0) {
    header('location:logout.php');
    exit();
} else {
    $chef_id = $_SESSION['GMScid'];
    
    // Traitement de l'upload du cachet
    if(isset($_POST['upload_stamp'])) {
        if(isset($_FILES['stamp_file']) && $_FILES['stamp_file']['error'] == 0) {
            $allowed_types = array('image/png', 'image/jpeg', 'image/jpg');
            $file_type = $_FILES['stamp_file']['type'];
            
            if(in_array($file_type, $allowed_types)) {
                $upload_dir = '../assets/stamps/';
                
                // Créer le dossier s'il n'existe pas
                if(!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_extension = pathinfo($_FILES['stamp_file']['name'], PATHINFO_EXTENSION);
                $new_filename = 'stamp_' . $chef_id . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                // Supprimer l'ancien cachet s'il existe
                $sql = "SELECT StampImage FROM tblusers WHERE ID=:uid";
                $query = $dbh->prepare($sql);
                $query->bindParam(':uid', $chef_id, PDO::PARAM_STR);
                $query->execute();
                $old_stamp = $query->fetch(PDO::FETCH_OBJ);
                
                if($old_stamp && $old_stamp->StampImage) {
                    $old_file = $upload_dir . $old_stamp->StampImage;
                    if(file_exists($old_file)) {
                        unlink($old_file);
                    }
                }
                
                if(move_uploaded_file($_FILES['stamp_file']['tmp_name'], $upload_path)) {
                    // Mettre à jour la base de données
                    $sql = "UPDATE tblusers SET StampImage=:stamp, StampUploadDate=NOW() WHERE ID=:uid";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':stamp', $new_filename, PDO::PARAM_STR);
                    $query->bindParam(':uid', $chef_id, PDO::PARAM_STR);
                    $query->execute();
                    
                    echo "<script>alert('Cachet officiel enregistré avec succès !'); window.location.href='stamp-management.php';</script>";
                } else {
                    echo "<script>alert('Erreur lors du téléchargement du fichier.');</script>";
                }
            } else {
                echo "<script>alert('Type de fichier non autorisé. Utilisez PNG ou JPEG.');</script>";
            }
        } else {
            echo "<script>alert('Aucun fichier sélectionné ou erreur lors du téléchargement.');</script>";
        }
    }
    
    // Supprimer le cachet
    if(isset($_GET['delete']) && $_GET['delete'] == 1) {
        $sql = "SELECT StampImage FROM tblusers WHERE ID=:uid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':uid', $chef_id, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_OBJ);
        
        if($result && $result->StampImage) {
            $file_path = '../assets/stamps/' . $result->StampImage;
            if(file_exists($file_path)) {
                unlink($file_path);
            }
            
            $sql = "UPDATE tblusers SET StampImage=NULL, StampUploadDate=NULL WHERE ID=:uid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':uid', $chef_id, PDO::PARAM_STR);
            $query->execute();
            
            echo "<script>alert('Cachet officiel supprimé avec succès !'); window.location.href='stamp-management.php';</script>";
        }
    }
    
    // Récupérer le cachet actuel
    $sql = "SELECT StampImage, StampUploadDate, Nom, Prenom, Fonction FROM tblusers WHERE ID=:uid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':uid', $chef_id, PDO::PARAM_STR);
    $query->execute();
    $chef_data = $query->fetch(PDO::FETCH_OBJ);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion du Cachet Officiel - Système de Gestion des Missions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <style>
        .stamp-preview {
            border: 3px dashed #6c757d;
            border-radius: 12px;
            padding: 30px;
            background-color: #f8f9fa;
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        
        .stamp-preview img {
            max-width: 100%;
            max-height: 250px;
            border: 2px solid #dee2e6;
            padding: 10px;
            background: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .upload-zone {
            border: 3px dashed #007bff;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            background: #f0f8ff;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .upload-zone:hover {
            background: #e6f2ff;
            border-color: #0056b3;
        }
        
        .upload-zone.drag-over {
            background: #cce5ff;
            border-color: #004085;
        }
        
        .info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <?php include_once('includes/sidebar.php'); ?>
    
    <div class="main-content">
        <?php include_once('includes/header.php'); ?>
        
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-stamp"></i> Gestion du Cachet Officiel</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Accueil</a></li>
                        <li class="breadcrumb-item active">Cachet Officiel</li>
                    </ol>
                </nav>
            </div>
            
            <!-- Informations du Chef -->
            <div class="info-card mb-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4><i class="fas fa-user-tie"></i> <?php echo htmlentities($chef_data->Nom . ' ' . $chef_data->Prenom); ?></h4>
                        <p class="mb-0"><strong>Fonction:</strong> <?php echo htmlentities($chef_data->Fonction); ?></p>
                    </div>
                    <div class="col-md-4 text-right">
                        <i class="fas fa-certificate fa-4x opacity-25"></i>
                    </div>
                </div>
            </div>
            
            <!-- Cachet actuel -->
            <?php if($chef_data && $chef_data->StampImage) { ?>
            <div class="alert alert-success">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5><i class="fas fa-check-circle"></i> Cachet Officiel Enregistré</h5>
                        <p class="mb-0">
                            <strong>Date d'enregistrement:</strong> <?php echo date('d/m/Y à H:i', strtotime($chef_data->StampUploadDate)); ?>
                        </p>
                        <p class="mb-0 mt-2">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> Ce cachet sera automatiquement apposé sur tous les ordres de mission validés.
                            </small>
                        </p>
                    </div>
                    <div class="col-md-4 text-right">
                        <a href="?delete=1" class="btn btn-danger" 
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer le cachet officiel ?\n\nAttention: Vous ne pourrez plus valider de missions tant qu\'un nouveau cachet n\'est pas téléchargé.')">
                            <i class="fas fa-trash"></i> Supprimer le Cachet
                        </a>
                    </div>
                </div>
            </div>
            <?php } else { ?>
            <div class="alert alert-warning">
                <h5><i class="fas fa-exclamation-triangle"></i> Aucun Cachet Officiel Enregistré</h5>
                <p class="mb-0">
                    Vous devez télécharger le cachet officiel pour pouvoir valider les demandes de mission. 
                    La signature manuscrite sera demandée lors de chaque validation.
                </p>
            </div>
            <?php } ?>
            
            <div class="row">
                <!-- Aperçu du cachet -->
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-eye"></i> Aperçu du Cachet Actuel</h5>
                        </div>
                        <div class="card-body">
                            <div class="stamp-preview">
                                <?php if($chef_data && $chef_data->StampImage) { ?>
                                    <img src="../assets/stamps/<?php echo htmlentities($chef_data->StampImage); ?>" 
                                         alt="Cachet officiel">
                                    <p class="mt-3 text-muted">
                                        <small><?php echo htmlentities($chef_data->StampImage); ?></small>
                                    </p>
                                <?php } else { ?>
                                    <i class="fas fa-stamp fa-5x text-muted mb-3"></i>
                                    <p class="text-muted">Aucun cachet enregistré</p>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Upload du cachet -->
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-cloud-upload-alt"></i> 
                                <?php echo ($chef_data && $chef_data->StampImage) ? 'Remplacer' : 'Télécharger'; ?> le Cachet Officiel
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data" id="upload-stamp-form">
                                <div class="upload-zone" id="upload-zone">
                                    <i class="fas fa-cloud-upload-alt fa-4x text-primary mb-3"></i>
                                    <h5>Glissez-déposez le fichier ici</h5>
                                    <p class="text-muted">ou cliquez pour sélectionner</p>
                                    <input type="file" class="d-none" id="stamp_file" name="stamp_file" 
                                           accept="image/png,image/jpeg,image/jpg" required>
                                </div>
                                
                                <div id="file-info" class="mt-3 d-none">
                                    <div class="alert alert-info">
                                        <strong><i class="fas fa-file-image"></i> Fichier sélectionné:</strong>
                                        <span id="file-name"></span>
                                        <br>
                                        <small>Taille: <span id="file-size"></span></small>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info mt-3">
                                    <h6><i class="fas fa-info-circle"></i> Recommandations:</h6>
                                    <ul class="mb-0 pl-3">
                                        <li>Utilisez une image PNG avec fond transparent pour un meilleur rendu</li>
                                        <li>Résolution recommandée: 300 DPI minimum</li>
                                        <li>Taille maximale: 5 MB</li>
                                        <li>Le cachet doit inclure le logo et la signature officielle</li>
                                        <li>Format carré ou rectangulaire (ratio 1:1 ou 4:3)</li>
                                    </ul>
                                </div>
                                
                                <button type="submit" name="upload_stamp" class="btn btn-success btn-block btn-lg mt-3">
                                    <i class="fas fa-save"></i> Enregistrer le Cachet Officiel
                                </button>
                            </form>
                            
                            <div class="mt-4 p-3 bg-light rounded">
                                <h6 class="text-primary"><i class="fas fa-question-circle"></i> Comment obtenir le cachet?</h6>
                                <ol class="mb-0 small">
                                    <li>Scannez le document officiel avec le cachet</li>
                                    <li>Ou photographiez-le avec un bon éclairage</li>
                                    <li>Utilisez un outil pour supprimer le fond (optionnel mais recommandé)</li>
                                    <li>Enregistrez en format PNG pour conserver la transparence</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Exemple de rendu dans le PDF -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-file-pdf"></i> Aperçu dans l'Ordre de Mission</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Voici comment la signature et le cachet apparaîtront dans le PDF généré:
                    </p>
                    
                    <div class="p-4 border rounded bg-white" style="max-width: 600px; margin: 0 auto;">
                        <p class="text-right mb-2"><em>Fait à El-Hamiz, le <?php echo date('d/m/Y'); ?>.</em></p>
                        <p class="text-right font-weight-bold mb-2">La Directrice des Ressources Humaines</p>
                        <p class="text-right font-weight-bold mb-3">et des Moyens Généraux</p>
                        
                        <div class="text-right">
                            <div style="display: inline-block; position: relative;">
                                <?php if($chef_data && $chef_data->StampImage) { ?>
                                <img src="../assets/stamps/<?php echo htmlentities($chef_data->StampImage); ?>" 
                                     alt="Cachet" style="max-width: 150px; opacity: 0.9;">
                                <?php } else { ?>
                                <div class="border border-danger p-3 text-danger">
                                    <i class="fas fa-stamp fa-2x"></i>
                                    <p class="small mb-0">Cachet Officiel</p>
                                </div>
                                <?php } ?>
                                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                                    <div class="text-center p-2" style="background: rgba(255,255,255,0.7); border: 2px dashed #007bff;">
                                        <p class="small mb-0" style="font-family: 'Brush Script MT', cursive; font-size: 20px; color: #000;">
                                            Signature
                                        </p>
                                        <small class="text-muted">(lors de validation)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <p class="text-right font-weight-bold text-decoration-underline mt-3">
                            <?php echo htmlentities(mb_substr($chef_data->Prenom, 0, 1) . '. ' . strtoupper($chef_data->Nom)); ?>
                        </p>
                    </div>
                    
                    <p class="text-center mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            La signature manuscrite sera superposée au cachet lors de la validation
                        </small>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    $(document).ready(function() {
        const uploadZone = $('#upload-zone');
        const fileInput = $('#stamp_file');
        const fileInfo = $('#file-info');
        const fileName = $('#file-name');
        const fileSize = $('#file-size');
        
        // Click sur la zone pour ouvrir le sélecteur
        uploadZone.click(function() {
            fileInput.click();
        });
        
        // Changement de fichier
        fileInput.change(function() {
            const file = this.files[0];
            if(file) {
                displayFileInfo(file);
            }
        });
        
        // Drag & Drop
        uploadZone.on('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('drag-over');
        });
        
        uploadZone.on('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('drag-over');
        });
        
        uploadZone.on('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('drag-over');
            
            const files = e.originalEvent.dataTransfer.files;
            if(files.length > 0) {
                fileInput[0].files = files;
                displayFileInfo(files[0]);
            }
        });
        
        function displayFileInfo(file) {
            // Vérifier le type
            const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
            if(!allowedTypes.includes(file.type)) {
                alert('Type de fichier non autorisé! Utilisez PNG ou JPEG.');
                fileInput.val('');
                return;
            }
            
            // Vérifier la taille (5 MB max)
            if(file.size > 5 * 1024 * 1024) {
                alert('Fichier trop volumineux! Taille maximale: 5 MB');
                fileInput.val('');
                return;
            }
            
            fileName.text(file.name);
            fileSize.text((file.size / 1024).toFixed(2) + ' KB');
            fileInfo.removeClass('d-none');
        }
        
        // Validation avant envoi
        $('#upload-stamp-form').submit(function(e) {
            if(!fileInput[0].files.length) {
                alert('Veuillez sélectionner un fichier!');
                e.preventDefault();
                return false;
            }
            
            return confirm('Êtes-vous sûr de vouloir enregistrer ce cachet officiel?\n\nCe cachet sera utilisé pour tous les ordres de mission que vous validerez.');
        });
    });
    </script>
</body>
</html>

<?php } ?>

