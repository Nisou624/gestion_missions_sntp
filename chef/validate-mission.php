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
        
        try {
            // Commencer la transaction
            $dbh->beginTransaction();
            
            // Mettre à jour le statut de la mission
            $sql = "UPDATE tblmissions SET Status=:status, Remarque=:remarque, ValidatedBy=:validator_id, DateValidation=NOW() WHERE ID=:mid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':status', $action, PDO::PARAM_STR);
            $query->bindParam(':remarque', $commentaire, PDO::PARAM_STR);
            $query->bindParam(':validator_id', $validator_id, PDO::PARAM_STR);
            $query->bindParam(':mid', $mid, PDO::PARAM_STR);
            $query->execute();
            
            // Enregistrer la validation dans l'historique
            $sql = "INSERT INTO tblmission_validations(MissionID, ValidatorID, Action, Commentaire, SignaturePath) 
                    VALUES(:mid, :validator_id, :action, :commentaire, :signature)";
            $query = $dbh->prepare($sql);
            $signature_path = 'assets/signatures/signature_' . $validator_id . '.png';
            $query->bindParam(':mid', $mid, PDO::PARAM_STR);
            $query->bindParam(':validator_id', $validator_id, PDO::PARAM_STR);
            $query->bindParam(':action', $action, PDO::PARAM_STR);
            $query->bindParam(':commentaire', $commentaire, PDO::PARAM_STR);
            $query->bindParam(':signature', $signature_path, PDO::PARAM_STR);
            $query->execute();
            
            // Valider la transaction
            $dbh->commit();
            
            $message = ($action == 'validee') ? 'Mission validée avec succès !' : 'Mission rejetée avec succès !';
            echo "<script>
                alert('$message');
                window.location.href='pending-missions.php';
            </script>";
            
        } catch(Exception $e) {
            // Annuler la transaction en cas d'erreur
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
                                    <small class="form-text text-muted">
                                        Ce commentaire sera visible par le demandeur.
                                    </small>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <small>
                                        En validant, vous apposez votre signature électronique sur cet ordre de mission.
                                    </small>
                                </div>
                                
                                <button type="submit" name="validate_mission" class="btn btn-success btn-block btn-lg">
                                    <i class="fas fa-signature"></i> Signer et Valider
                                </button>
                                
                                <a href="pending-missions.php" class="btn btn-secondary btn-block mt-2">
                                    <i class="fas fa-arrow-left"></i> Retour à la Liste
                                </a>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6><i class="fas fa-clock"></i> Informations Temporelles</h6>
                        </div>
                        <div class="card-body">
                            <?php
                            $dateDepart = new DateTime($mission->DateDepart);
                            $aujourd_hui = new DateTime();
                            $diff = $dateDepart->diff($aujourd_hui);
                            
                            if($dateDepart > $aujourd_hui) {
                                echo '<p class="text-success"><i class="fas fa-calendar-check"></i> Mission dans <strong>'.$diff->days.' jours</strong></p>';
                            } else {
                                echo '<p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Mission prévue il y a <strong>'.$diff->days.' jours</strong></p>';
                            }
                            
                            $dateCreation = new DateTime($mission->DateCreation);
                            $diffCreation = $aujourd_hui->diff($dateCreation);
                            echo '<p class="text-muted"><i class="fas fa-file"></i> Demande créée il y a <strong>'.$diffCreation->days.' jour(s)</strong></p>';
                            ?>
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
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    $(document).ready(function() {
        $('#action').change(function() {
            var action = $(this).val();
            var commentaire = $('#commentaire');
            
            if(action == 'rejetee') {
                commentaire.attr('required', true);
                commentaire.attr('placeholder', 'Motif du rejet (obligatoire)...');
                commentaire.closest('.form-group').find('label').html('Commentaire/Remarque <span class="text-danger">*</span>:');
            } else {
                commentaire.removeAttr('required');
                commentaire.attr('placeholder', 'Ajoutez vos commentaires ou remarques...');
                commentaire.closest('.form-group').find('label').html('Commentaire/Remarque:');
            }
        });
        
        $('#validationForm').submit(function(e) {
            var action = $('#action').val();
            var commentaire = $('#commentaire').val();
            
            if(!action) {
                alert('Veuillez choisir une action de validation !');
                e.preventDefault();
                return false;
            }
            
            if(action == 'rejetee' && !commentaire.trim()) {
                alert('Le motif du rejet est obligatoire !');
                e.preventDefault();
                return false;
            }
            
            var message = action == 'validee' ? 
                'Êtes-vous sûr de vouloir VALIDER cette mission ?' : 
                'Êtes-vous sûr de vouloir REJETER cette mission ?';
                
            return confirm(message);
        });
    });
    </script>
</body>
</html>

<?php } ?>
