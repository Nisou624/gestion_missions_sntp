<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['GMSaid']) == 0) {
    header('location:logout.php');
} else {
    $vid = intval($_GET['viewid']);
    
    if(isset($_POST['update_status'])) {
        $status = $_POST['status'];
        $remarque = $_POST['remarque'];
        $validator_id = $_SESSION['GMSaid'];
        
        $sql = "UPDATE tblmissions SET Status=:status, Remarque=:remarque, ValidatedBy=:validator_id WHERE ID=:vid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':status', $status, PDO::PARAM_STR);
        $query->bindParam(':remarque', $remarque, PDO::PARAM_STR);
        $query->bindParam(':validator_id', $validator_id, PDO::PARAM_STR);
        $query->bindParam(':vid', $vid, PDO::PARAM_STR);
        $query->execute();
        
        echo "<script>alert('Statut mis à jour avec succès !');</script>";
        echo "<script>window.location.href = 'view-mission-detail.php?viewid=".$vid."'</script>";
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail Mission - Système de Gestion des Missions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include_once('includes/sidebar.php'); ?>
    
    <div class="main-content">
        <?php include_once('includes/header.php'); ?>
        
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-eye"></i> Détail de la Mission</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Accueil</a></li>
                        <li class="breadcrumb-item"><a href="all-missions.php">Missions</a></li>
                        <li class="breadcrumb-item active">Détail</li>
                    </ol>
                </nav>
            </div>
            
            <?php
            $sql = "SELECT m.*, u.Nom, u.Prenom, u.Email, u.MobileNumber, u.Departement, u.Fonction as UserFonction,
                           v.Nom as ValidatorNom, v.Prenom as ValidatorPrenom
                    FROM tblmissions m 
                    JOIN tblusers u ON m.UserID = u.ID 
                    LEFT JOIN tblusers v ON m.ValidatedBy = v.ID
                    WHERE m.ID = :vid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':vid', $vid, PDO::PARAM_STR);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_OBJ);
            
            if($query->rowCount() > 0) {
            ?>
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-info-circle"></i> Informations de la Mission</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="200">Numéro de Référence:</th>
                                    <td><strong><?php echo htmlentities($result->ReferenceNumber); ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Demandeur:</th>
                                    <td><?php echo htmlentities($result->Nom . ' ' . $result->Prenom); ?></td>
                                </tr>
                                <tr>
                                    <th>Fonction:</th>
                                    <td><?php echo htmlentities($result->UserFonction); ?></td>
                                </tr>
                                <tr>
                                    <th>Département:</th>
                                    <td><?php echo htmlentities($result->Departement); ?></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td><?php echo htmlentities($result->Email); ?></td>
                                </tr>
                                <tr>
                                    <th>Téléphone:</th>
                                    <td><?php echo htmlentities($result->MobileNumber); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5><i class="fas fa-map-marker-alt"></i> Détails du Déplacement</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="200">Ville de Départ:</th>
                                    <td><?php echo htmlentities($result->VilleDepart); ?></td>
                                </tr>
                                <tr>
                                    <th>Date de Départ:</th>
                                    <td><?php echo date('d/m/Y', strtotime($result->DateDepart)); ?></td>
                                </tr>
                                <tr>
                                    <th>Destinations:</th>
                                    <td><?php echo htmlentities($result->Destinations); ?></td>
                                </tr>
                                <tr>
                                    <th>Type d'Itinéraire:</th>
                                    <td><?php echo htmlentities($result->ItineraireType); ?></td>
                                </tr>
                                <tr>
                                    <th>Date de Retour:</th>
                                    <td><?php echo date('d/m/Y', strtotime($result->DateRetour)); ?></td>
                                </tr>
                                <tr>
                                    <th>Motif:</th>
                                    <td><?php echo htmlentities($result->MotifDeplacement); ?></td>
                                </tr>
                                <tr>
                                    <th>Moyen de Transport:</th>
                                    <td><?php echo htmlentities($result->MoyenTransport); ?></td>
                                </tr>
                                <?php if($result->Observations) { ?>
                                <tr>
                                    <th>Observations:</th>
                                    <td><?php echo htmlentities($result->Observations); ?></td>
                                </tr>
                                <?php } ?>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-cog"></i> Statut et Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label>Statut Actuel:</label>
                                <div>
                                    <?php if($result->Status == 'en_attente') { ?>
                                        <span class="badge badge-warning badge-lg">
                                            <i class="fas fa-clock"></i> En attente de validation
                                        </span>
                                    <?php } elseif($result->Status == 'validee') { ?>
                                        <span class="badge badge-success badge-lg">
                                            <i class="fas fa-check-circle"></i> Validée
                                        </span>
                                    <?php } elseif($result->Status == 'rejetee') { ?>
                                        <span class="badge badge-danger badge-lg">
                                            <i class="fas fa-times-circle"></i> Rejetée
                                        </span>
                                    <?php } ?>
                                </div>
                            </div>
                            
                            <?php if($result->ValidatorNom) { ?>
                            <div class="mb-3">
                                <label>Validée par:</label>
                                <p><?php echo htmlentities($result->ValidatorNom . ' ' . $result->ValidatorPrenom); ?></p>
                            </div>
                            <?php } ?>
                            
                            <?php if($result->Remarque) { ?>
                            <div class="mb-3">
                                <label>Remarque:</label>
                                <p class="text-muted"><?php echo htmlentities($result->Remarque); ?></p>
                            </div>
                            <?php } ?>
                            
                            <div class="mb-3">
                                <label>Date de Demande:</label>
                                <p><?php echo date('d/m/Y H:i', strtotime($result->DateCreation)); ?></p>
                            </div>
                            
                            <?php if($result->Status == 'validee') { ?>
                            <a href="../includes/generate-pdf.php?mid=<?php echo $result->ID; ?>" 
                               class="btn btn-success btn-block" target="_blank">
                                <i class="fas fa-file-pdf"></i> Générer l'Ordre de Mission (PDF)
                            </a>
                            <?php } ?>
                        </div>
                    </div>
                    
                    <!-- Actions administrateur -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6><i class="fas fa-tools"></i> Actions Administrateur</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="form-group">
                                    <label for="status">Changer le Statut:</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="en_attente" <?php if($result->Status=='en_attente') echo 'selected'; ?>>En attente</option>
                                        <option value="validee" <?php if($result->Status=='validee') echo 'selected'; ?>>Validée</option>
                                        <option value="rejetee" <?php if($result->Status=='rejetee') echo 'selected'; ?>>Rejetée</option>
                                        <option value="en_cours" <?php if($result->Status=='en_cours') echo 'selected'; ?>>En cours</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="remarque">Remarque:</label>
                                    <textarea name="remarque" id="remarque" class="form-control" rows="3"><?php echo htmlentities($result->Remarque); ?></textarea>
                                </div>
                                
                                <button type="submit" name="update_status" class="btn btn-primary btn-block">
                                    <i class="fas fa-save"></i> Mettre à Jour
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php } else { ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                Mission non trouvée.
            </div>
            <?php } ?>
        </div>
    </div>
    
    <?php include_once('includes/footer.php'); ?>
</body>
</html>

<?php } ?>
