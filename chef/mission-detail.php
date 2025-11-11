<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['GMScid']) == 0) {
    header('location:logout.php');
} else {
    $mid = intval($_GET['mid']);
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
                <h2><i class="fas fa-info-circle"></i> Détail de la Mission</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Accueil</a></li>
                        <li class="breadcrumb-item active">Détail Mission</li>
                    </ol>
                </nav>
            </div>
            
            <?php
            $sql = "SELECT m.*, u.Nom, u.Prenom, u.Email, u.MobileNumber, u.Departement, u.Fonction as UserFonction,
                           v.Nom as ValidatorNom, v.Prenom as ValidatorPrenom, v.Fonction as ValidatorFonction
                    FROM tblmissions m 
                    JOIN tblusers u ON m.UserID = u.ID 
                    LEFT JOIN tblusers v ON m.ValidatedBy = v.ID
                    WHERE m.ID = :mid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':mid', $mid, PDO::PARAM_STR);
            $query->execute();
            $mission = $query->fetch(PDO::FETCH_OBJ);
            
            if($query->rowCount() > 0) {
            ?>
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5><i class="fas fa-file-alt"></i> Informations Complètes de la Mission</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-success">👤 Informations du Demandeur</h6>
                                    <table class="table table-sm table-borderless">
                                        <tr><th width="120">Nom Complet:</th><td><?php echo htmlentities($mission->Nom . ' ' . $mission->Prenom); ?></td></tr>
                                        <tr><th>Fonction:</th><td><?php echo htmlentities($mission->UserFonction); ?></td></tr>
                                        <tr><th>Département:</th><td><?php echo htmlentities($mission->Departement); ?></td></tr>
                                        <tr><th>Email:</th><td><?php echo htmlentities($mission->Email); ?></td></tr>
                                        <tr><th>Téléphone:</th><td><?php echo htmlentities($mission->MobileNumber ?: 'Non renseigné'); ?></td></tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-success">📋 Informations de la Mission</h6>
                                    <table class="table table-sm table-borderless">
                                        <tr><th width="120">Référence:</th><td><strong><?php echo htmlentities($mission->ReferenceNumber); ?></strong></td></tr>
                                        <tr><th>Date Demande:</th><td><?php echo date('d/m/Y H:i', strtotime($mission->DateCreation)); ?></td></tr>
                                        <tr><th>Statut:</th><td>
                                            <?php if($mission->Status == 'en_attente') { ?>
                                                <span class="badge badge-warning">En attente</span>
                                            <?php } elseif($mission->Status == 'validee') { ?>
                                                <span class="badge badge-success">Validée</span>
                                            <?php } elseif($mission->Status == 'rejetee') { ?>
                                                <span class="badge badge-danger">Rejetée</span>
                                            <?php } else { ?>
                                                <span class="badge badge-info">En cours</span>
                                            <?php } ?>
                                        </td></tr>
                                        <?php if($mission->DateValidation) { ?>
                                        <tr><th>Date Validation:</th><td><?php echo date('d/m/Y H:i', strtotime($mission->DateValidation)); ?></td></tr>
                                        <?php } ?>
                                        <?php if($mission->ValidatorNom) { ?>
                                        <tr><th>Validé par:</th><td><?php echo htmlentities($mission->ValidatorNom . ' ' . $mission->ValidatorPrenom); ?></td></tr>
                                        <?php } ?>
                                    </table>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <h6 class="text-success">🗺️ Détails du Déplacement</h6>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <tr>
                                        <th width="200">Ville de Départ:</th>
                                        <td><?php echo htmlentities($mission->VilleDepart); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Destinations:</th>
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
                                        <th>Date de Départ:</th>
                                        <td><strong><?php echo date('d/m/Y', strtotime($mission->DateDepart)); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th>Date de Retour:</th>
                                        <td><strong><?php echo date('d/m/Y', strtotime($mission->DateRetour)); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th>Durée:</th>
                                        <td>
                                            <?php 
                                            $dateDepart = new DateTime($mission->DateDepart);
                                            $dateRetour = new DateTime($mission->DateRetour);
                                            $duree = $dateDepart->diff($dateRetour);
                                            echo $duree->days . ' jour(s)';
                                            ?>
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
                                    <?php if($mission->Remarque) { ?>
                                    <tr>
                                        <th>Remarque/Commentaire:</th>
                                        <td class="<?php echo $mission->Status == 'rejetee' ? 'text-danger' : 'text-info'; ?>">
                                            <i class="fas fa-comment"></i>
                                            <?php echo nl2br(htmlentities($mission->Remarque)); ?>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h6><i class="fas fa-cogs"></i> Actions</h6>
                        </div>
                        <div class="card-body">
                            <?php if($mission->Status == 'en_attente') { ?>
                            <a href="validate-mission.php?mid=<?php echo $mission->ID; ?>" 
                               class="btn btn-warning btn-block text-white">
                                <i class="fas fa-signature"></i> Valider cette Mission
                            </a>
                            <?php } ?>
                            
                            <?php if($mission->Status == 'validee') { ?>
                            <a href="../includes/generate-pdf.php?mid=<?php echo $mission->ID; ?>" 
                               class="btn btn-success btn-block" target="_blank">
                                <i class="fas fa-file-pdf"></i> Générer PDF
                            </a>
                            <?php } ?>
                            
                            <a href="all-missions.php" class="btn btn-secondary btn-block mt-2">
                                <i class="fas fa-arrow-left"></i> Retour à la Liste
                            </a>
                        </div>
                    </div>
                    
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6><i class="fas fa-calendar-alt"></i> Échéances</h6>
                        </div>
                        <div class="card-body">
                            <?php
                            $dateDepart = new DateTime($mission->DateDepart);
                            $dateRetour = new DateTime($mission->DateRetour);
                            $aujourd_hui = new DateTime();
                            ?>
                            
                            <?php if($dateDepart > $aujourd_hui) { ?>
                            <div class="alert alert-info alert-sm">
                                <strong>Mission dans :</strong><br>
                                <?php 
                                $diff = $aujourd_hui->diff($dateDepart);
                                echo $diff->days . ' jour(s)';
                                ?>
                            </div>
                            <?php } elseif($dateDepart <= $aujourd_hui && $dateRetour >= $aujourd_hui) { ?>
                            <div class="alert alert-warning alert-sm">
                                <strong>Mission en cours !</strong><br>
                                Retour prévu dans <?php 
                                $diff = $aujourd_hui->diff($dateRetour);
                                echo $diff->days . ' jour(s)';
                                ?>
                            </div>
                            <?php } else { ?>
                            <div class="alert alert-success alert-sm">
                                <strong>Mission terminée</strong><br>
                                Il y a <?php 
                                $diff = $dateRetour->diff($aujourd_hui);
                                echo $diff->days . ' jour(s)';
                                ?>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                    
                    <?php if($mission->Status == 'validee') { ?>
                    <div class="card mt-3">
                        <div class="card-header bg-success text-white">
                            <h6><i class="fas fa-stamp"></i> Validation</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-success">
                                <i class="fas fa-check-circle"></i>
                                <strong>Mission validée et signée</strong>
                            </p>
                            <small class="text-muted">
                                Le <?php echo date('d/m/Y à H:i', strtotime($mission->DateValidation)); ?><br>
                                Par <?php echo htmlentities($mission->ValidatorNom . ' ' . $mission->ValidatorPrenom); ?>
                            </small>
                        </div>
                    </div>
                    <?php } ?>
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
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php } ?>
