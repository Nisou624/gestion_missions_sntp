<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../includes/config.php');

if (strlen($_SESSION['GMScid']) == 0) {
    header('location:logout.php');
    exit();
} else {
    $chef_id = $_SESSION['GMScid'];
    
    // Traitement de la suppression du cachet
    if(isset($_GET['delete']) && $_GET['delete'] == 1) {
        $sql = "SELECT StampImage FROM tblusers WHERE ID=:uid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':uid', $chef_id, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_OBJ);
        
        if($result && !empty($result->StampImage)) {
            $file_path = '../assets/stamps/' . $result->StampImage;
            
            // Vérifier et supprimer réellement le fichier
            if(file_exists($file_path)) {
                if(unlink($file_path)) {
                    error_log("Cachet supprimé: " . $file_path);
                } else {
                    error_log("Erreur lors de la suppression du cachet: " . $file_path);
                }
            }
            
            // Mettre à jour la base de données
            $sql = "UPDATE tblusers SET StampImage=NULL, StampDate=NULL WHERE ID=:uid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':uid', $chef_id, PDO::PARAM_STR);
            $query->execute();
            
            echo "<script>alert('Cachet supprimé avec succès !'); window.location.href='stamp-management.php';</script>";
            exit();
        }
    }
    
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
                
                // Supprimer l'ancien cachet avant d'uploader le nouveau
                $sql = "SELECT StampImage FROM tblusers WHERE ID=:uid";
                $query = $dbh->prepare($sql);
                $query->bindParam(':uid', $chef_id, PDO::PARAM_STR);
                $query->execute();
                $old_stamp = $query->fetch(PDO::FETCH_OBJ);
                
                if($old_stamp && !empty($old_stamp->StampImage)) {
                    $old_file_path = $upload_dir . $old_stamp->StampImage;
                    if(file_exists($old_file_path)) {
                        unlink($old_file_path);
                        error_log("Ancien cachet supprimé: " . $old_file_path);
                    }
                }
                
                // Ajouter un timestamp pour éviter le cache du navigateur
                $file_extension = pathinfo($_FILES['stamp_file']['name'], PATHINFO_EXTENSION);
                $timestamp = time();
                $new_filename = 'stamp_' . $chef_id . '_' . $timestamp . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                // Vérifier la taille du fichier (5 MB max)
                if($_FILES['stamp_file']['size'] > 5 * 1024 * 1024) {
                    echo "<script>alert('Le fichier est trop volumineux ! Taille maximale: 5 MB'); window.location.href='stamp-management.php';</script>";
                    exit();
                }
                
                if(move_uploaded_file($_FILES['stamp_file']['tmp_name'], $upload_path)) {
                    // Mettre à jour la base de données
                    $sql = "UPDATE tblusers SET StampImage=:stamp, StampDate=NOW() WHERE ID=:uid";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':stamp', $new_filename, PDO::PARAM_STR);
                    $query->bindParam(':uid', $chef_id, PDO::PARAM_STR);
                    $query->execute();
                    
                    echo "<script>alert('Cachet officiel enregistré avec succès !'); window.location.href='stamp-management.php?refresh=" . time() . "';</script>";
                    exit();
                } else {
                    echo "<script>alert('Erreur lors du téléchargement du fichier.');</script>";
                }
            } else {
                echo "<script>alert('Type de fichier non autorisé. Utilisez PNG ou JPEG.');</script>";
            }
        } else {
            $error_msg = '';
            switch($_FILES['stamp_file']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $error_msg = 'Le fichier est trop volumineux.';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $error_msg = 'Aucun fichier n\'a été uploadé.';
                    break;
                default:
                    $error_msg = 'Erreur lors de l\'upload : ' . $_FILES['stamp_file']['error'];
            }
            echo "<script>alert('" . $error_msg . "');</script>";
        }
    }
    
    // Récupérer le cachet actuel avec gestion des colonnes manquantes
    $sql = "SELECT StampImage, StampDate FROM tblusers WHERE ID=:uid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':uid', $chef_id, PDO::PARAM_STR);
    $query->execute();
    $user_stamp = $query->fetch(PDO::FETCH_OBJ);
    
    // CORRECTION : Initialiser les propriétés si elles n'existent pas
    if($user_stamp && !isset($user_stamp->StampImage)) {
        $user_stamp->StampImage = null;
    }
    if($user_stamp && !isset($user_stamp->StampDate)) {
        $user_stamp->StampDate = null;
    }
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
            border: 2px dashed #6c757d;
            border-radius: 8px;
            padding: 20px;
            background-color: #f8f9fa;
            min-height: 250px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        .stamp-preview img {
            max-width: 100%;
            max-height: 230px;
            border: 1px solid #ddd;
            padding: 10px;
            background: white;
        }
        
        .upload-area {
            border: 3px dashed #007bff;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            background-color: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .upload-area:hover {
            background-color: #e9ecef;
            border-color: #0056b3;
        }
        
        .upload-area.dragover {
            background-color: #d4edff;
            border-color: #0056b3;
        }
        
        .card {
            transition: all 0.3s;
        }
        
        .card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
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
                        <li class="breadcrumb-item active">Gestion Cachet</li>
                    </ol>
                </nav>
            </div>
            
            <!-- Fonction actuelle du chef -->
            <div class="alert alert-info mb-4">
                <strong>Fonction:</strong> Chef de Département Audit
            </div>
            
            <!-- Cachet actuel -->
            <?php if($user_stamp && !empty($user_stamp->StampImage)) { 
                // Ajouter timestamp à l'URL pour éviter le cache
                $cache_buster = '?v=' . time();
                
                // CORRECTION : Vérifier si StampDate existe et n'est pas null
                $display_date = '01/01/1970 à 01:00';
                if(!empty($user_stamp->StampDate)) {
                    $display_date = date('d/m/Y à H:i', strtotime($user_stamp->StampDate));
                }
            ?>
            <div class="card border-success mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-check-circle"></i> Cachet Officiel Enregistré</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <p><strong>Date d'enregistrement:</strong> <?php echo htmlentities($display_date); ?></p>
                            <p class="text-muted">Ce cachet sera automatiquement apposé sur tous les ordres de mission validés.</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="stamp-preview">
                                <img src="../assets/stamps/<?php echo htmlentities($user_stamp->StampImage . $cache_buster); ?>" 
                                     alt="Cachet Officiel" 
                                     id="current-stamp-image">
                            </div>
                            <button onclick="confirmDelete()" class="btn btn-danger btn-sm mt-3">
                                <i class="fas fa-trash"></i> Supprimer le Cachet
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php } else { ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> 
                Aucun cachet officiel enregistré. Veuillez uploader votre cachet ci-dessous.
            </div>
            <?php } ?>
            
            <!-- Formulaire d'upload -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-cloud-upload-alt"></i> 
                        <?php echo ($user_stamp && !empty($user_stamp->StampImage)) ? 'Remplacer le Cachet Officiel' : 'Enregistrer le Cachet Officiel'; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" id="stamp-upload-form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="upload-area" id="upload-area">
                                    <i class="fas fa-cloud-upload-alt fa-4x text-primary mb-3"></i>
                                    <h5>Glissez-déposez le fichier ici</h5>
                                    <p class="text-muted mb-3">ou cliquez pour sélectionner</p>
                                    <input type="file" 
                                           class="d-none" 
                                           id="stamp_file" 
                                           name="stamp_file" 
                                           accept="image/png,image/jpeg,image/jpg" 
                                           required>
                                    <button type="button" class="btn btn-primary" id="select-file-btn">
                                        <i class="fas fa-folder-open"></i> Sélectionner un Fichier
                                    </button>
                                </div>
                                
                                <div class="mt-3" id="file-info" style="display: none;">
                                    <div class="alert alert-info">
                                        <i class="fas fa-file-image"></i> 
                                        <strong>Fichier sélectionné:</strong> <span id="file-name"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="stamp-preview" id="preview-container">
                                    <div class="text-center text-muted">
                                        <i class="fas fa-image fa-4x mb-3"></i>
                                        <p>Aperçu du cachet</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" name="upload_stamp" class="btn btn-success btn-lg btn-block" id="submit-btn" disabled>
                                <i class="fas fa-save"></i> Enregistrer le Cachet Officiel
                            </button>
                        </div>
                    </form>
                    
                    <div class="alert alert-info mt-4">
                        <h6><i class="fas fa-info-circle"></i> Recommandations:</h6>
                        <ul class="mb-0">
                            <li>Utilisez une image PNG avec fond transparent pour un meilleur rendu</li>
                            <li>Résolution recommandée: 300 DPI minimum</li>
                            <li>Taille maximale: 5 MB</li>
                            <li>Le cachet doit inclure le logo et la signature officielle</li>
                            <li>Format carré ou rectangulaire (ratio 1:1 ou 4:3)</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-secondary mt-3">
                        <h6><i class="fas fa-question-circle"></i> Comment obtenir le cachet?</h6>
                        <ol class="mb-0">
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
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // CORRECTION MAJEURE : Éviter la récursion infinie de jQuery
    (function($) {
        'use strict';
        
        const fileInput = document.getElementById('stamp_file');
        const uploadArea = document.getElementById('upload-area');
        const previewContainer = document.getElementById('preview-container');
        const fileInfo = document.getElementById('file-info');
        const fileName = document.getElementById('file-name');
        const submitBtn = document.getElementById('submit-btn');
        const selectFileBtn = document.getElementById('select-file-btn');
        
        // CORRECTION : Utiliser des événements natifs au lieu de jQuery pour éviter la récursion
        selectFileBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            fileInput.click();
        });
        
        uploadArea.addEventListener('click', function(e) {
            // Ne déclencher que si on ne clique pas sur le bouton
            if(e.target !== selectFileBtn && !selectFileBtn.contains(e.target)) {
                fileInput.click();
            }
        });
        
        // Gestion du changement de fichier
        fileInput.addEventListener('change', function() {
            if(this.files.length > 0) {
                handleFile(this.files[0]);
            }
        });
        
        // Drag & Drop
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if(files.length > 0) {
                // Assigner manuellement le fichier à l'input
                const dt = new DataTransfer();
                dt.items.add(files[0]);
                fileInput.files = dt.files;
                handleFile(files[0]);
            }
        });
        
        function handleFile(file) {
            if(!file) return;
            
            // Vérifier le type
            const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
            if(!allowedTypes.includes(file.type)) {
                alert('Type de fichier non autorisé ! Utilisez PNG, JPG ou JPEG.');
                fileInput.value = '';
                return;
            }
            
            // Vérifier la taille (5 MB)
            if(file.size > 5 * 1024 * 1024) {
                alert('Le fichier est trop volumineux ! Taille maximale: 5 MB');
                fileInput.value = '';
                return;
            }
            
            // Afficher le nom du fichier
            fileName.textContent = file.name;
            fileInfo.style.display = 'block';
            
            // Activer le bouton submit
            submitBtn.disabled = false;
            
            // Prévisualisation
            const reader = new FileReader();
            reader.onload = function(event) {
                previewContainer.innerHTML = '<img src="' + event.target.result + '" alt="Aperçu du cachet">';
            };
            reader.readAsDataURL(file);
        }
        
        // Confirmation avant suppression
        window.confirmDelete = function() {
            if(confirm('Êtes-vous sûr de vouloir supprimer ce cachet officiel ?\n\nCette action est irréversible !')) {
                window.location.href = 'stamp-management.php?delete=1';
            }
        };
        
        // Confirmation avant upload
        document.getElementById('stamp-upload-form').addEventListener('submit', function(e) {
            if(!fileInput.files.length) {
                alert('Veuillez sélectionner un fichier !');
                e.preventDefault();
                return false;
            }
            
            return confirm('Êtes-vous sûr de vouloir enregistrer ce cachet ?\n\nIl remplacera l\'ancien cachet s\'il existe.');
        });
        
        // Forcer le rechargement de l'image actuelle sans cache
        const currentStampImage = document.getElementById('current-stamp-image');
        if(currentStampImage) {
            const src = currentStampImage.src.split('?')[0];
            currentStampImage.src = src + '?v=' + new Date().getTime();
        }
        
    })(jQuery);
    </script>
</body>
</html>

<?php } ?>

