<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['GMScid']) == 0) {
    header('location:logout.php');
    exit();
} else {
    $mid = intval($_GET['mid']);
    $chef_id = $_SESSION['GMScid'];
    
    // Vérifier si le chef a un cachet enregistré
    $sql = "SELECT StampImage FROM tblusers WHERE ID=:uid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':uid', $chef_id, PDO::PARAM_STR);
    $query->execute();
    $chef_stamp = $query->fetch(PDO::FETCH_OBJ);
    
    if(isset($_POST['validate_mission'])) {
        $action = $_POST['action'];
        $commentaire = $_POST['commentaire'];
        $validator_id = $_SESSION['GMScid'];
        
        try {
            $dbh->beginTransaction();
            
            // Gérer la signature manuscrite (obligatoire pour validation)
            $signature_filename = null;
            $stamp_filename = null;
            
            if($action == 'validee') {
                // Vérifier qu'une signature a été fournie
                if(empty($_POST['signature_data'])) {
                    throw new Exception('La signature est obligatoire pour valider une mission!');
                }
                
                // Enregistrer la signature dessinée
                $signature_data = $_POST['signature_data'];
                $image_data = str_replace('data:image/png;base64,', '', $signature_data);
                $image_data = str_replace(' ', '+', $image_data);
                $decoded_image = base64_decode($image_data);
                
                $upload_dir = '../assets/signatures/';
                if(!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $signature_filename = 'signature_mission_' . $mid . '_' . time() . '.png';
                if(!file_put_contents($upload_dir . $signature_filename, $decoded_image)) {
                    throw new Exception('Erreur lors de l\'enregistrement de la signature');
                }
                
                // Récupérer le cachet du chef
                if($chef_stamp && $chef_stamp->StampImage) {
                    $stamp_filename = $chef_stamp->StampImage;
                }
            }
            
            // Mettre à jour le statut de la mission
            $sql = "UPDATE tblmissions SET 
                    Status=:status, 
                    Remarque=:remarque, 
                    ValidatedBy=:validator_id, 
                    DateValidation=NOW(),
                    SignaturePath=:signature,
                    StampPath=:stamp
                    WHERE ID=:mid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':status', $action, PDO::PARAM_STR);
            $query->bindParam(':remarque', $commentaire, PDO::PARAM_STR);
            $query->bindParam(':validator_id', $validator_id, PDO::PARAM_STR);
            $query->bindParam(':signature', $signature_filename, PDO::PARAM_STR);
            $query->bindParam(':stamp', $stamp_filename, PDO::PARAM_STR);
            $query->bindParam(':mid', $mid, PDO::PARAM_STR);
            $query->execute();
            
            $dbh->commit();
            
            $message = ($action == 'validee') ? 'Mission validée avec succès !' : 'Mission rejetée avec succès !';
            echo "<script>
                alert('$message');
                window.location.href='pending-missions.php';
            </script>";
            exit();
            
        } catch(Exception $e) {
            $dbh->rollback();
            echo "<script>alert('Erreur lors de la validation: " . $e->getMessage() . "');</script>";
        }
    }
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
            border: 3px solid #007bff;
            border-radius: 8px;
            background-color: #ffffff;
            cursor: crosshair;
            touch-action: none;
            width: 100%;
        }
        
        .stamp-overlay {
            position: relative;
            min-height: 250px;
            border: 2px dashed #6c757d;
            border-radius: 8px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .stamp-overlay img {
            max-width: 200px;
            max-height: 200px;
            opacity: 0.3;
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
            // Vérifier si le chef a un cachet
            if(!$chef_stamp || !$chef_stamp->StampImage) {
            ?>
            <div class="alert alert-danger">
                <h5><i class="fas fa-exclamation-triangle"></i> Cachet Officiel Manquant!</h5>
                <p>Vous devez d'abord enregistrer votre cachet officiel avant de pouvoir valider des missions.</p>
                <a href="stamp-management.php" class="btn btn-warning">
                    <i class="fas fa-stamp"></i> Gérer le Cachet Officiel
                </a>
            </div>
            <?php
                echo "</div></div></body></html>";
                exit();
            }
            
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
                            <form method="POST" id="validationForm" action="">
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
                                    <textarea name="commentaire" id="commentaire" class="form-control" rows="3" 
                                            placeholder="Ajoutez vos commentaires ou remarques..."></textarea>
                                </div>
                                
                                <input type="hidden" name="signature_data" id="signature_data_hidden" value="">
                                <input type="hidden" name="validate_mission" value="1">
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <small>
                                        Vous devrez signer manuellement avant la validation.
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
    <div class="modal fade" id="signatureModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-signature"></i> Signature Manuscrite Obligatoire
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Important:</strong> Signez dans le cadre ci-dessous. Votre signature sera apposée sur le cachet officiel dans le PDF généré.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-7">
                            <h6>Dessinez votre signature:</h6>
                            <canvas id="signature-pad-modal" class="signature-canvas" width="700" height="300"></canvas>
                            <div class="mt-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <label class="mr-2"><i class="fas fa-pen"></i> Épaisseur:</label>
                                    <input type="range" id="stroke-width-modal" min="1" max="6" value="3" style="width: 120px;">
                                    <span id="stroke-value-modal" class="badge badge-primary">3</span>px
                                </div>
                                <button type="button" class="btn btn-warning" id="clear-signature-modal">
                                    <i class="fas fa-eraser"></i> Effacer
                                </button>
                            </div>
                        </div>
                        
                        <div class="col-md-5">
                            <h6>Aperçu avec le cachet officiel:</h6>
                            <div class="stamp-overlay" id="preview-overlay">
                                <?php if($chef_stamp && $chef_stamp->StampImage) { ?>
                                <img src="../assets/stamps/<?php echo htmlentities($chef_stamp->StampImage); ?>" 
                                     alt="Cachet" id="stamp-preview">
                                <?php } ?>
                                <canvas id="preview-canvas" 
                                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                                </canvas>
                            </div>
                            <p class="text-center mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    Aperçu du rendu final dans le PDF
                                </small>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                    <button type="button" class="btn btn-success btn-lg" id="confirm-signature">
                        <i class="fas fa-check-circle"></i> Confirmer la Signature et Valider
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
        const previewCanvas = document.getElementById('preview-canvas');
        const previewCtx = previewCanvas ? previewCanvas.getContext('2d') : null;
        const strokeWidthInput = document.getElementById('stroke-width-modal');
        const strokeValueDisplay = document.getElementById('stroke-value-modal');
        
        let isDrawing = false;
        let lastX = 0;
        let lastY = 0;
        let strokeWidth = 3;
        let hasDrawn = false;
        let signaturePaths = [];
        
        if(ctx) {
            ctx.strokeStyle = '#000080';
            ctx.lineJoin = 'round';
            ctx.lineCap = 'round';
            ctx.lineWidth = strokeWidth;
            
            if(previewCtx) {
                previewCanvas.width = previewCanvas.offsetWidth;
                previewCanvas.height = previewCanvas.offsetHeight;
                previewCtx.strokeStyle = '#000080';
                previewCtx.lineJoin = 'round';
                previewCtx.lineCap = 'round';
            }
            
            strokeWidthInput.addEventListener('input', function() {
                strokeWidth = this.value;
                strokeValueDisplay.textContent = this.value;
                ctx.lineWidth = strokeWidth;
            });
            
            function startDrawing(e) {
                isDrawing = true;
                hasDrawn = true;
                const rect = canvas.getBoundingClientRect();
                lastX = e.clientX - rect.left;
                lastY = e.clientY - rect.top;
                signaturePaths.push({type: 'start', x: lastX, y: lastY, width: strokeWidth});
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
                
                signaturePaths.push({type: 'draw', x: currentX, y: currentY});
                [lastX, lastY] = [currentX, currentY];
                updatePreview();
            }
            
            function stopDrawing() {
                if(isDrawing) signaturePaths.push({type: 'end'});
                isDrawing = false;
            }
            
            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseout', stopDrawing);
            
            function startDrawingTouch(e) {
                e.preventDefault();
                isDrawing = true;
                hasDrawn = true;
                const rect = canvas.getBoundingClientRect();
                const touch = e.touches[0];
                lastX = touch.clientX - rect.left;
                lastY = touch.clientY - rect.top;
                signaturePaths.push({type: 'start', x: lastX, y: lastY, width: strokeWidth});
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
                
                signaturePaths.push({type: 'draw', x: currentX, y: currentY});
                [lastX, lastY] = [currentX, currentY];
                updatePreview();
            }
            
            function stopDrawingTouch(e) {
                e.preventDefault();
                if(isDrawing) signaturePaths.push({type: 'end'});
                isDrawing = false;
            }
            
            canvas.addEventListener('touchstart', startDrawingTouch);
            canvas.addEventListener('touchmove', drawTouch);
            canvas.addEventListener('touchend', stopDrawingTouch);
            canvas.addEventListener('touchcancel', stopDrawingTouch);
            
            function updatePreview() {
                if(!previewCtx) return;
                previewCtx.clearRect(0, 0, previewCanvas.width, previewCanvas.height);
                
                const scaleX = previewCanvas.width / canvas.width;
                const scaleY = previewCanvas.height / canvas.height;
                const scale = Math.min(scaleX, scaleY) * 0.6;
                
                const offsetX = (previewCanvas.width - canvas.width * scale) / 2;
                const offsetY = (previewCanvas.height - canvas.height * scale) / 2;
                
                previewCtx.save();
                previewCtx.translate(offsetX, offsetY);
                previewCtx.scale(scale, scale);
                
                signaturePaths.forEach((path) => {
                    if(path.type === 'start') {
                        previewCtx.beginPath();
                        previewCtx.moveTo(path.x, path.y);
                        previewCtx.lineWidth = path.width;
                    } else if(path.type === 'draw') {
                        previewCtx.lineTo(path.x, path.y);
                        previewCtx.stroke();
                    }
                });
                
                previewCtx.restore();
            }
            
            document.getElementById('clear-signature-modal').addEventListener('click', function() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                if(previewCtx) previewCtx.clearRect(0, 0, previewCanvas.width, previewCanvas.height);
                hasDrawn = false;
                signaturePaths = [];
            });
        }
        
        $('#open-signature-modal').click(function(e) {
            e.preventDefault();
            const action = $('#action').val();
            const commentaire = $('#commentaire').val();
            
            if(!action) {
                alert('Veuillez choisir une action de validation !');
                return false;
            }
            
            if(action == 'rejetee') {
                if(!commentaire.trim()) {
                    alert('Le motif du rejet est obligatoire !');
                    return false;
                }
                if(confirm('Êtes-vous sûr de vouloir REJETER cette mission ?')) {
                    document.getElementById('validationForm').submit();
                }
                return false;
            }
            
            $('#signatureModal').modal('show');
            return false;
        });
        
        $('#confirm-signature').click(function(e) {
            e.preventDefault();
            
            if(!hasDrawn) {
                alert('Veuillez dessiner votre signature avant de valider !');
                return false;
            }
            
            console.log('Conversion de la signature en base64...');
            const signatureData = canvas.toDataURL('image/png');
            $('#signature_data_hidden').val(signatureData);
            
            console.log('Signature enregistrée, longueur:', signatureData.length);
            
            $('#signatureModal').modal('hide');
            
            setTimeout(function() {
                console.log('Soumission du formulaire...');
                document.getElementById('validationForm').submit();
            }, 500);
            
            return false;
        });
        
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

