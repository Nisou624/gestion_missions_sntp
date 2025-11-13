<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['GMSuid']) == 0) {
    header('location:logout.php');
} else {
    $uid = $_SESSION['GMSuid'];
    $mid = intval($_GET['mid']);
    
    // CORRECTION: Vérifier que la mission appartient à l'utilisateur OU qu'il l'a soumise
    $sql = "SELECT m.*, 
                   u.Nom as MissionNom, u.Prenom as MissionPrenom, u.Fonction as MissionFonction, u.Departement as MissionDept,
                   s.Nom as SubmitterNom, s.Prenom as SubmitterPrenom, s.Fonction as SubmitterFonction,
                   v.Nom as ValidatorNom, v.Prenom as ValidatorPrenom
            FROM tblmissions m 
            LEFT JOIN tblusers u ON m.UserID = u.ID 
            LEFT JOIN tblusers s ON m.SubmittedBy = s.ID
            LEFT JOIN tblusers v ON m.ValidatedBy = v.ID
            WHERE m.ID = :mid AND (m.UserID = :uid OR m.SubmittedBy = :uid2)";
    $query = $dbh->prepare($sql);
    $query->bindParam(':mid', $mid, PDO::PARAM_STR);
    $query->bindParam(':uid', $uid, PDO::PARAM_STR);
    $query->bindParam(':uid2', $uid, PDO::PARAM_STR);
    $query->execute();
    
    if($query->rowCount() == 0) {
        echo "<script>alert('Mission non trouvée ou vous n\'êtes pas autorisé à la consulter !'); window.location.href='my-missions.php';</script>";
        exit();
    }
    
    $mission = $query->fetch(PDO::FETCH_OBJ);
    
    // Déterminer le type de relation avec cette mission
    $is_beneficiary = ($mission->UserID == $uid);
    $is_submitter = ($mission->SubmittedBy == $uid);
    $is_both = ($is_beneficiary && $is_submitter);
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
                        <li class="breadcrumb-item"><a href="my-missions.php">Mes Missions</a></li>
                        <li class="breadcrumb-item active">Détail</li>
                    </ol>
                </nav>
            </div>
            
            <!-- Alerte indiquant la relation avec cette mission -->
            <div class="alert <?php echo $is_both ? 'alert-success' : ($is_beneficiary ? 'alert-info' : 'alert-warning'); ?>">
                <i class="fas <?php echo $is_both ? 'fa-user-check' : ($is_beneficiary ? 'fa-gift' : 'fa-paper-plane'); ?>"></i>
                <strong>Votre rôle dans cette mission :</strong>
                <?php if($is_both) { ?>
                    Vous êtes le bénéficiaire ET vous avez créé cette demande
                <?php } elseif($is_beneficiary) { ?>
                    Vous êtes le bénéficiaire de cette mission (créée par <?php echo htmlentities($mission->SubmitterNom . ' ' . $mission->SubmitterPrenom); ?>)
                <?php } else { ?>
                    Vous avez créé cette demande pour <?php echo htmlentities($mission->MissionNom . ' ' . $mission->MissionPrenom); ?>
                <?php } ?>
            </div>
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5><i class="fas fa-info-circle"></i> Informations de la Mission</h5>
                        </div>
                        <div class="card-body">
                            <!-- Informations sur qui va en mission -->
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-info">👤 Employé en Mission</h6>
                                    <table class="table table-sm table-borderless">
                                        <tr><th width="120">Nom Complet:</th><td><?php echo htmlentities($mission->MissionNom . ' ' . $mission->MissionPrenom); ?></td></tr>
                                        <tr><th>Fonction:</th><td><?php echo htmlentities($mission->MissionFonction); ?></td></tr>
                                        <tr><th>Département:</th><td><?php echo htmlentities($mission->MissionDept); ?></td></tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-info">📝 Informations de la Demande</h6>
                                    <table class="table table-sm table-borderless">
                                        <tr><th width="120">Référence:</th><td><strong><?php echo htmlentities($mission->ReferenceNumber); ?></strong></td></tr>
                                        <tr><th>Date Demande:</th><td><?php echo date('d/m/Y H:i', strtotime($mission->DateCreation)); ?></td></tr>
                                        <tr><th>Créée par:</th><td>
                                            <?php if($mission->SubmittedBy == $mission->UserID) { ?>
                                                <span class="text-success">
                                                    <i class="fas fa-user"></i> L'employé lui-même
                                                </span>
                                            <?php } else { ?>
                                                <span class="text-primary">
                                                    <i class="fas fa-user-friends"></i>
                                                    <?php echo htmlentities($mission->SubmitterNom . ' ' . $mission->SubmitterPrenom); ?>
                                                </span>
                                                <br><small class="text-muted"><?php echo htmlentities($mission->SubmitterFonction); ?></small>
                                            <?php } ?>
                                        </td></tr>
                                    </table>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <h6 class="text-info">🗺️ Détails du Déplacement</h6>
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
                        <div class="card-header">
                            <h5><i class="fas fa-traffic-light"></i> Statut de la Mission</h5>
                        </div>
                        <div class="card-body text-center">
                            <?php if($mission->Status == 'en_attente') { ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-clock fa-3x mb-3"></i>
                                    <h5>En Attente de Validation</h5>
                                    <p class="mb-0">La demande est en cours d'examen par le chef de département.</p>
                                </div>
                            <?php } elseif($mission->Status == 'validee') { ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle fa-3x mb-3"></i>
                                    <h5>Mission Validée</h5>
                                    <p class="mb-0">L'ordre de mission a été validé et signé.</p>
                                </div>
                            <?php } elseif($mission->Status == 'rejetee') { ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-times-circle fa-3x mb-3"></i>
                                    <h5>Mission Rejetée</h5>
                                    <p class="mb-0">La demande a été rejetée.</p>
                                </div>
                            <?php } ?>
                            
                            <div class="mt-3">
                                <strong>Date de Demande:</strong><br>
                                <?php echo date('d/m/Y à H:i', strtotime($mission->DateCreation)); ?>
                            </div>
                            
                            <?php if($mission->DateValidation) { ?>
                            <div class="mt-2">
                                <strong>Date de Traitement:</strong><br>
                                <?php echo date('d/m/Y à H:i', strtotime($mission->DateValidation)); ?>
                            </div>
                            <?php } ?>
                            
                            <?php if($mission->ValidatorNom) { ?>
                            <div class="mt-2">
                                <strong>Validé par:</strong><br>
                                <?php echo htmlentities($mission->ValidatorNom . ' ' . $mission->ValidatorPrenom); ?>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6><i class="fas fa-cogs"></i> Actions Disponibles</h6>
                        </div>
                        <div class="card-body">
                            <?php if($mission->Status == 'en_attente' && $is_submitter) { ?>
                            <a href="edit-mission.php?mid=<?php echo $mission->ID; ?>" 
                               class="btn btn-warning btn-block">
                                <i class="fas fa-edit"></i> Modifier cette Demande
                            </a>
                            <small class="text-muted d-block mb-3">
                                <i class="fas fa-info-circle"></i> Vous pouvez modifier car vous avez créé cette demande
                            </small>
                            <?php } ?>
                            
                            <?php if($mission->Status == 'validee') { ?>
                            <a href="../includes/generate-pdf.php?mid=<?php echo $mission->ID; ?>" 
                               class="btn btn-success btn-block" target="_blank">
                                <i class="fas fa-print"></i> Imprimer l'Ordre de Mission
                            </a>
                            <?php } ?>
                            
                            <a href="my-missions.php" class="btn btn-secondary btn-block mt-2">
                                <i class="fas fa-arrow-left"></i> Retour à Mes Missions
                            </a>
                            
                            <?php if($mission->Status == 'rejetee' && $is_submitter) { ?>
                            <a href="create-mission.php" class="btn btn-primary btn-block mt-2">
                                <i class="fas fa-plus"></i> Créer une Nouvelle Demande
                            </a>
                            <?php } ?>
                        </div>
                    </div>
                    
                    <!-- Informations temporelles -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6><i class="fas fa-calendar"></i> Échéances</h6>
                        </div>
                        <div class="card-body">
                            <?php
                            $dateDepart = new DateTime($mission->DateDepart);
                            $dateRetour = new DateTime($mission->DateRetour);
                            $aujourd_hui = new DateTime();
                            
                            $duree = $dateDepart->diff($dateRetour);
                            $jours_mission = $duree->days;
                            
                            $diff_depart = $dateDepart->diff($aujourd_hui);
                            ?>
                            
                            <p><strong>Durée de la Mission:</strong><br>
                            <span class="badge badge-info"><?php echo $jours_mission; ?> jour(s)</span></p>
                            
                            <?php if($dateDepart > $aujourd_hui) { ?>
                            <p><strong>Mission dans:</strong><br>
                            <span class="text-success">
                                <i class="fas fa-calendar-plus"></i> 
                                <?php echo $diff_depart->days; ?> jour(s)
                            </span></p>
                            <?php } elseif($dateDepart <= $aujourd_hui && $dateRetour >= $aujourd_hui) { ?>
                            <p><strong>Mission en cours</strong><br>
                            <span class="text-info">
                                <i class="fas fa-plane"></i>
                                Retour dans <?php echo $aujourd_hui->diff($dateRetour)->days; ?> jour(s)
                            </span></p>
                            <?php } else { ?>
                            <p><strong>Mission terminée</strong><br>
                            <span class="text-muted">
                                <i class="fas fa-check"></i>
                                Il y a <?php echo $aujourd_hui->diff($dateRetour)->days; ?> jour(s)
                            </span></p>
                            <?php } ?>
                        </div>
                    </div>
                    
                    <!-- Informations sur les autorisations -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6><i class="fas fa-key"></i> Vos Droits</h6>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <?php if($is_beneficiary) { ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-user text-success"></i> Bénéficiaire</span>
                                    <span class="badge badge-success">Oui</span>
                                </div>
                                <?php } ?>
                                
                                <?php if($is_submitter) { ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-paper-plane text-primary"></i> Créateur</span>
                                    <span class="badge badge-primary">Oui</span>
                                </div>
                                <?php } ?>
                                
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-eye text-info"></i> Consultation</span>
                                    <span class="badge badge-info">Autorisé</span>
                                </div>
                                
                                <?php if($mission->Status == 'validee') { ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-print text-success"></i> Impression PDF</span>
                                    <span class="badge badge-success">Autorisé</span>
                                </div>
                                <?php } ?>
                                
                                <?php if($mission->Status == 'en_attente' && $is_submitter) { ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-edit text-warning"></i> Modification</span>
                                    <span class="badge badge-warning">Autorisé</span>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php } ?>
