<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['GMScid']) == 0) {
    header('location:logout.php');
} else {
    $mid = intval($_GET['mid']);
    
    if(isset($_POST['validate_mission'])) {
        $action = $_POST['action'];
        $commentaire = $_POST['commentaire'];
        $validator_id = $_SESSION['GMScid'];
        $signature_method = $_POST['signature_method'];
        
        try {
            $dbh->beginTransaction();
            
            // Gérer la signature
            $signature_filename = null;
            
            if($action == 'validee') {
                if($signature_method == 'drawn' && !empty($_POST['signature_data'])) {
                    // Signature dessinée
                    $signature_data = $_POST['signature_data'];
                    $image_data = str_replace('data:image/png;base64,', '', $signature_data);
                    $image_data = str_replace(' ', '+', $image_data);
                    $decoded_image = base64_decode($image_data);
                    
                    $upload_dir = '../assets/signatures/';
                    if(!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $signature_filename = 'signature_mission_' . $mid . '_' . time() . '.png';
                    file_put_contents($upload_dir . $signature_filename, $decoded_image);
                    
                } elseif($signature_method == 'existing') {
                    // Utiliser la signature existante
                    $sql = "SELECT SignatureImage FROM tblusers WHERE ID=:uid";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':uid', $validator_id, PDO::PARAM_STR);
                    $query->execute();
                    $result = $query->fetch(PDO::FETCH_OBJ);
                    
                    if($result && $result->SignatureImage) {
                        $signature_filename = $result->SignatureImage;
                    }
                }
            }
            
            // Mettre à jour le statut de la mission
            $sql = "UPDATE tblmissions SET Status=:status, Remarque=:remarque, ValidatedBy=:validator_id, 
                    DateValidation=NOW(), SignaturePath=:signature WHERE ID=:mid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':status', $action, PDO::PARAM_STR);
            $query->bindParam(':remarque', $commentaire, PDO::PARAM_STR);
            $query->bindParam(':validator_id', $validator_id, PDO::PARAM_STR);
            $query->bindParam(':signature', $signature_filename, PDO::PARAM_STR);
            $query->bindParam(':mid', $mid, PDO::PARAM_STR);
            $query->execute();
            
            $dbh->commit();
            
            $message = ($action == 'validee') ? 'Mission validée avec succès !' : 'Mission rejetée avec succès !';
            echo "<script>
                alert('$message');
                window.location.href='pending-missions.php';
            </script>";
            
        } catch(Exception $e) {
            $dbh->rollback();
            echo "<script>alert('Erreur lors de la validation: " . $e->getMessage() . "');</script>";
        }
    }
    
    // Récupérer la signature existante du chef
    $sql = "SELECT SignatureImage, SignatureType FROM tblusers WHERE ID=:uid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':uid', $_SESSION['GMScid'], PDO::PARAM_STR);
    $query->execute();
    $chef_signature = $query->fetch(PDO::FETCH_OBJ);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation Mission - Système de Gestion des Missions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <style>
        .signature-canvas {
            border: 2px solid #007bff;
            border-radius: 5px;
            background-color: #ffffff;
            cursor: crosshair;
            touch-action: none;
            width: 100%;
        }
        
        .signature-preview {
            border: 2px dashed #6c757d;
            padding: 10px;
            background-color: #f8f9fa;
            text-align: center;
            min-height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .signature-preview img {
            max-width: 100%;
            max-height: 140px;
        }
    </style>
</head>
<body>
    <?php include_once('includes/sidebar.php'); ?>
    
    <div class="main-content">
        <?php include_once('includes/header.php'); ?>
        
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-clipboard-check"></i> Validation de Mission</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Accueil</a></li>
                        <li class="breadcrumb-item"><a href="pending-missions.php">Missions en Attente</a></li>
                        <li class="breadcrumb-item active">Validation</li>
                    </ol>
                </nav>
            </div>
            
            <?php
            $sql = "SELECT m.*, u.Nom, u.Prenom, u.Email, u.MobileNumber, u.Departement, u.Fonction as UserFonction
                    FROM tblmissions m 
                    JOIN tblusers u ON m.UserID = u.ID 
                    WHERE m.ID = :mid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':mid', $mid, PDO::PARAM_STR);
            $query->execute();
            $mission = $query->fetch(PDO::FETCH_OBJ);
            
            if($query->rowCount() > 0) {
            ?>
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <h5><i class="fas fa-info-circle"></i> Détails de la Demande de Mission</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary">Informations du Demandeur</h6>
                                    <table class="table table-sm table-borderless">
                                        <tr><th>Nom Complet:</th><td><?php echo htmlentities($mission->Nom . ' ' . $mission->Prenom); ?></td></tr>
                                        <tr><th>Fonction:</th><td><?php echo htmlentities($mission->UserFonction); ?></td></tr>
                                        <tr><th>Département:</th><td><?php echo htmlentities($mission->Departement); ?></td></tr>
                                        <tr><th>Email:</th><td><?php echo htmlentities($mission->Email); ?></td></tr>
                                        <tr><th>Téléphone:</th><td><?php echo htmlentities($mission->MobileNumber); ?></td></tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-primary">Informations de la Mission</h6>
                                    <table class="table table-sm table-borderless">
                                        <tr><th>Référence:</th><td><strong><?php echo htmlentities($mission->ReferenceNumber); ?></strong></td></tr>
                                        <tr><th>Date de Demande:</th><td><?php echo date('d/m/Y H:i', strtotime($mission->DateCreation)); ?></td></tr>
                                        <tr><th>Ville de Départ:</th><td><?php echo htmlentities($mission->VilleDepart); ?></td></tr>
                                        <tr><th>Date de Départ:</th><td><?php echo date('d/m/Y', strtotime($mission->DateDepart)); ?></td></tr>
                                        <tr><th>Date de Retour:</th><td><?php echo date('d/m/Y', strtotime($mission->DateRetour)); ?></td></tr>
                                    </table>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-primary">Détails du Déplacement</h6>
                                    <table class="table table-striped">
                                        <tr>
                                            <th width="200">Destinations:</th>
                                            <td><?php echo htmlentities($mission->Destinations); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Type d'Itinéraire:</th>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?php echo htmlentities($mission->ItineraireType); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Motif du Déplacement:</th>
                                            <td>
                                                <span class="badge badge-secondary">
                                                    <?php echo htmlentities($mission->MotifDeplacement); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Moyen de Transport:</th>
                                            <td><?php echo htmlentities($mission->MoyenTransport); ?></td>
                                        </tr>
                                        <?php if($mission->Observations) { ?>
                                        <tr>
                                            <th>Observations:</th>
                                            <td><?php echo nl2br(htmlentities($mission->Observations)); ?></td>
                                        </tr>
                                        <?php } ?>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h5><i class="fas fa-signature"></i> Validation Électronique</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" id="validationForm">
                                <div class="form-group">
                                    <label for="action">Décision de Validation <span class="text-danger">*</span>:</label>
                                    <select name="action" id="action" class="form-control" required>
                                        <option value="">-- Choisir une action --</option>
                                        <option value="validee" class="text-success">✓ Valider la Mission</option>
                                        <option value="rejetee" class="text-danger">✗ Rejeter la Mission</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="commentaire">Commentaire/Remarque:</label>
                                    <textarea name="commentaire" id="commentaire" class="form-control" rows="4" 
                                            placeholder="Ajoutez vos commentaires ou remarques..."></textarea>
                                </div>
                                
                                <input type="hidden" name="signature_method" id="signature_method">
                                <input type="hidden" name="signature_data" id="signature_data_hidden">
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <small>
                                        En validant, vous apposez votre signature électronique sur cet ordre de mission.
                                    </small>
                                </div>
                                
                                <button type="button" class="btn btn-success btn-block btn-lg" id="open-signature-modal">
                                    <i class="fas fa-signature"></i> Signer et Valider
                                </button>
                                
                                <a href="pending-missions.php" class="btn btn-secondary btn-block mt-2">
                                    <i class="fas fa-arrow-left"></i> Retour à la Liste
                                </a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php } else { ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                Mission non trouvée ou déjà traitée.
            </div>
            <?php } ?>
        </div>
    </div>
    
    <!-- Modal de Signature -->
    <div class="modal fade" id="signatureModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-signature"></i> Apposer votre Signature</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="signatureTabs" role="tablist">
                        <?php if($chef_signature && $chef_signature->SignatureImage) { ?>
                        <li class="nav-item">
                            <a class="nav-link active" id="existing-tab" data-toggle="tab" href="#existing" role="tab">
                                <i class="fas fa-check-circle"></i> Utiliser ma signature enregistrée
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="draw-tab" data-toggle="tab" href="#draw" role="tab">
                                <i class="fas fa-pen"></i> Dessiner une nouvelle signature
                            </a>
                        </li>
                        <?php } else { ?>
                        <li class="nav-item">
                            <a class="nav-link active" id="draw-tab" data-toggle="tab" href="#draw" role="tab">
                                <i class="fas fa-pen"></i> Dessiner ma signature
                            </a>
                        </li>
                        <?php } ?>
                    </ul>
                    
                    <div class="tab-content mt-3" id="signatureTabContent">
                        <?php if($chef_signature && $chef_signature->SignatureImage) { ?>
                        <!-- Onglet Signature Existante -->
                        <div class="tab-pane fade show active" id="existing" role="tabpanel">
                            <div class="text-center">
                                <p class="text-muted">Votre signature enregistrée sera utilisée pour ce document:</p>
                                <div class="signature-preview">
                                    <img src="../assets/signatures/<?php echo htmlentities($chef_signature->SignatureImage); ?>" 
                                         alt="Ma signature">
                                </div>
                                <p class="mt-3">
                                    <small class="text-muted">
                                        Type: <?php echo ($chef_signature->SignatureType == 'drawn') ? 'Signature Dessinée' : 'Image Scannée'; ?>
                                    </small>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Onglet Dessiner -->
                        <div class="tab-pane fade" id="draw" role="tabpanel">
                        <?php } else { ?>
                        <div class="tab-pane fade show active" id="draw" role="tabpanel">
                        <?php } ?>
                            <p class="text-muted">Dessinez votre signature dans le cadre ci-dessous:</p>
                            <canvas id="signature-pad-modal" class="signature-canvas" width="700" height="200"></canvas>
                            <div class="mt-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <label class="mr-2">Épaisseur:</label>
                                    <input type="range" id="stroke-width-modal" min="1" max="5" value="2" style="width: 100px;">
                                    <span id="stroke-value-modal">2</span>px
                                </div>
                                <button type="button" class="btn btn-warning btn-sm" id="clear-signature-modal">
                                    <i class="fas fa-eraser"></i> Effacer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                    <button type="button" class="btn btn-success" id="confirm-signature">
                        <i class="fas fa-check"></i> Confirmer et Valider
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    $(document).ready(function() {
        const canvas = document.getElementById('signature-pad-modal');
        const ctx = canvas ? canvas.getContext('2d') : null;
        const strokeWidthInput = document.getElementById('stroke-width-modal');
        const strokeValueDisplay = document.getElementById('stroke-value-modal');
        
        let isDrawing = false;
        let lastX = 0;
        let lastY = 0;
        let strokeWidth = 2;
        let hasDrawn = false;
        
        if(ctx) {
            // Configuration du canvas
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
                hasDrawn = true;
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
            
            // Fonctions tactiles
            function startDrawingTouch(e) {
                e.preventDefault();
                isDrawing = true;
                hasDrawn = true;
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
            
            // Effacer
            document.getElementById('clear-signature-modal').addEventListener('click', function() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                hasDrawn = false;
            });
        }
        
        // Vérification de l'action avant d'ouvrir le modal
        $('#open-signature-modal').click(function() {
            const action = $('#action').val();
            const commentaire = $('#commentaire').val();
            
            if(!action) {
                alert('Veuillez choisir une action de validation !');
                return;
            }
            
            if(action == 'rejetee' && !commentaire.trim()) {
                alert('Le motif du rejet est obligatoire !');
                return;
            }
            
            if(action == 'rejetee') {
                if(confirm('Êtes-vous sûr de vouloir REJETER cette mission ?')) {
                    $('#validationForm').submit();
                }
                return;
            }
            
            // Ouvrir le modal pour validation
            $('#signatureModal').modal('show');
        });
        
        // Confirmation de la signature
        $('#confirm-signature').click(function() {
            const activeTab = $('.nav-link.active').attr('href');
            
            if(activeTab === '#existing') {
                // Utiliser la signature existante
                $('#signature_method').val('existing');
                $('#validationForm').submit();
                
            } else if(activeTab === '#draw') {
                // Vérifier si une signature a été dessinée
                if(!hasDrawn) {
                    alert('Veuillez dessiner votre signature avant de valider !');
                    return;
                }
                
                // Convertir le canvas en base64
                const signatureData = canvas.toDataURL('image/png');
                $('#signature_method').val('drawn');
                $('#signature_data_hidden').val(signatureData);
                $('#validationForm').submit();
            }
        });
        
        // Validation du formulaire
        $('#action').change(function() {
            var action = $(this).val();
            var commentaire = $('#commentaire');
            
            if(action == 'rejetee') {
                commentaire.attr('required', true);
                commentaire.attr('placeholder', 'Motif du rejet (obligatoire)...');
            } else {
                commentaire.removeAttr('required');
                commentaire.attr('placeholder', 'Ajoutez vos commentaires ou remarques...');
            }
        });
    });
    </script>
</body>
</html>

<?php } ?>

