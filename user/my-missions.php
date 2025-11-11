<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['GMSuid']) == 0) {
    header('location:logout.php');
} else {
    $uid = $_SESSION['GMSuid'];
    
    // Message de succès
    $success_message = '';
    if(isset($_SESSION['success_message'])) {
        $success_message = $_SESSION['success_message'];
        unset($_SESSION['success_message']);
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Demandes de Mission - Système de Gestion des Missions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css" rel="stylesheet">
</head>
<body>
    <?php include_once('includes/sidebar.php'); ?>
    
    <div class="main-content">
        <?php include_once('includes/header.php'); ?>
        
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-list"></i> Mes Demandes de Mission</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Accueil</a></li>
                        <li class="breadcrumb-item active">Mes Demandes</li>
                    </ol>
                </nav>
            </div>
            
            <!-- Message de succès -->
            <?php if($success_message) { ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlentities($success_message); ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
            <?php } ?>
            
            <!-- Onglets pour séparer les demandes -->
            <ul class="nav nav-tabs" id="missionTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="for-me-tab" data-toggle="tab" href="#for-me" role="tab">
                        <i class="fas fa-user"></i> Missions pour Moi
                        <span class="badge badge-info ml-1" id="for-me-count">0</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="by-me-tab" data-toggle="tab" href="#by-me" role="tab">
                        <i class="fas fa-paper-plane"></i> Demandes que j'ai Soumises
                        <span class="badge badge-primary ml-1" id="by-me-count">0</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="all-tab" data-toggle="tab" href="#all" role="tab">
                        <i class="fas fa-list"></i> Toutes mes Demandes
                        <span class="badge badge-secondary ml-1" id="all-count">0</span>
                    </a>
                </li>
            </ul>
            
            <div class="tab-content" id="missionTabsContent">
                <!-- Missions pour moi -->
                <div class="tab-pane fade show active" id="for-me" role="tabpanel">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">Missions dont je suis le Bénéficiaire</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="forMeTable">
                                    <thead>
                                        <tr>
                                            <th>Référence</th>
                                            <th>Destinations</th>
                                            <th>Date Départ</th>
                                            <th>Motif</th>
                                            <th>Soumise par</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT m.*, s.Nom as SubmitterNom, s.Prenom as SubmitterPrenom, s.Fonction as SubmitterFonction
                                               FROM tblmissions m 
                                               LEFT JOIN tblusers s ON m.SubmittedBy = s.ID
                                               WHERE m.UserID = :uid 
                                               ORDER BY m.DateCreation DESC";
                                        $query = $dbh->prepare($sql);
                                        $query->bindParam(':uid', $uid, PDO::PARAM_STR);
                                        $query->execute();
                                        $for_me_missions = $query->fetchAll(PDO::FETCH_OBJ);
                                        
                                        if(count($for_me_missions) > 0) {
                                            foreach($for_me_missions as $row) {
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlentities($row->ReferenceNumber); ?></strong></td>
                                            <td><?php echo htmlentities($row->Destinations); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($row->DateDepart)); ?></td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?php echo htmlentities($row->MotifDeplacement); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if($row->SubmittedBy == $uid) { ?>
                                                    <span class="text-success">
                                                        <i class="fas fa-user"></i> Moi-même
                                                    </span>
                                                <?php } else { ?>
                                                    <span class="text-primary">
                                                        <i class="fas fa-user-friends"></i>
                                                        <?php echo htmlentities($row->SubmitterNom . ' ' . $row->SubmitterPrenom); ?>
                                                    </span>
                                                    <br><small class="text-muted"><?php echo htmlentities($row->SubmitterFonction); ?></small>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <?php if($row->Status == 'en_attente') { ?>
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-clock"></i> En attente
                                                    </span>
                                                <?php } elseif($row->Status == 'validee') { ?>
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check-circle"></i> Validée
                                                    </span>
                                                <?php } elseif($row->Status == 'rejetee') { ?>
                                                    <span class="badge badge-danger">
                                                        <i class="fas fa-times-circle"></i> Rejetée
                                                    </span>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="view-mission.php?mid=<?php echo $row->ID; ?>" 
                                                       class="btn btn-sm btn-info" title="Voir">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if($row->Status == 'validee') { ?>
                                                    <a href="print-mission.php?mid=<?php echo $row->ID; ?>" 
                                                       class="btn btn-sm btn-success" title="PDF" target="_blank">
                                                        <i class="fas fa-print"></i>
                                                    </a>
                                                    <?php } ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php }
                                        } else { ?>
                                        <tr><td colspan="7" class="text-center text-muted">Aucune mission pour vous</td></tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Demandes que j'ai soumises -->
                <div class="tab-pane fade" id="by-me" role="tabpanel">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Demandes que j'ai Créées</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="byMeTable">
                                    <thead>
                                        <tr>
                                            <th>Référence</th>
                                            <th>Employé en Mission</th>
                                            <th>Destinations</th>
                                            <th>Date Départ</th>
                                            <th>Motif</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT m.*, u.Nom as MissionNom, u.Prenom as MissionPrenom, u.Fonction as MissionFonction
                                               FROM tblmissions m 
                                               LEFT JOIN tblusers u ON m.UserID = u.ID
                                               WHERE m.SubmittedBy = :uid 
                                               ORDER BY m.DateCreation DESC";
                                        $query = $dbh->prepare($sql);
                                        $query->bindParam(':uid', $uid, PDO::PARAM_STR);
                                        $query->execute();
                                        $by_me_missions = $query->fetchAll(PDO::FETCH_OBJ);
                                        
                                        if(count($by_me_missions) > 0) {
                                            foreach($by_me_missions as $row) {
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlentities($row->ReferenceNumber); ?></strong></td>
                                            <td>
                                                <?php if($row->UserID == $uid) { ?>
                                                    <span class="text-success">
                                                        <i class="fas fa-user"></i> Moi-même
                                                    </span>
                                                <?php } else { ?>
                                                    <span class="text-info">
                                                        <i class="fas fa-user"></i>
                                                        <?php echo htmlentities($row->MissionNom . ' ' . $row->MissionPrenom); ?>
                                                    </span>
                                                    <br><small class="text-muted"><?php echo htmlentities($row->MissionFonction); ?></small>
                                                <?php } ?>
                                            </td>
                                            <td><?php echo htmlentities($row->Destinations); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($row->DateDepart)); ?></td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?php echo htmlentities($row->MotifDeplacement); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if($row->Status == 'en_attente') { ?>
                                                    <span class="badge badge-warning">En attente</span>
                                                <?php } elseif($row->Status == 'validee') { ?>
                                                    <span class="badge badge-success">Validée</span>
                                                <?php } elseif($row->Status == 'rejetee') { ?>
                                                    <span class="badge badge-danger">Rejetée</span>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="view-mission.php?mid=<?php echo $row->ID; ?>" 
                                                       class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if($row->Status == 'validee') { ?>
                                                    <a href="print-mission.php?mid=<?php echo $row->ID; ?>" 
                                                       class="btn btn-sm btn-success" target="_blank">
                                                        <i class="fas fa-print"></i>
                                                    </a>
                                                    <?php } ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php }
                                        } else { ?>
                                        <tr><td colspan="7" class="text-center text-muted">Aucune demande soumise</td></tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Toutes les demandes -->
                <div class="tab-pane fade" id="all" role="tabpanel">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">Toutes mes Demandes (Créées + Bénéficiaire)</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="allTable">
                                    <thead>
                                        <tr>
                                            <th>Référence</th>
                                            <th>Type</th>
                                            <th>Employé en Mission</th>
                                            <th>Destinations</th>
                                            <th>Date Départ</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT m.*, 
                                                       u.Nom as MissionNom, u.Prenom as MissionPrenom, u.Fonction as MissionFonction,
                                                       s.Nom as SubmitterNom, s.Prenom as SubmitterPrenom
                                               FROM tblmissions m 
                                               LEFT JOIN tblusers u ON m.UserID = u.ID
                                               LEFT JOIN tblusers s ON m.SubmittedBy = s.ID
                                               WHERE m.UserID = :uid OR m.SubmittedBy = :uid2
                                               ORDER BY m.DateCreation DESC";
                                        $query = $dbh->prepare($sql);
                                        $query->bindParam(':uid', $uid, PDO::PARAM_STR);
                                        $query->bindParam(':uid2', $uid, PDO::PARAM_STR);
                                        $query->execute();
                                        $all_missions = $query->fetchAll(PDO::FETCH_OBJ);
                                        
                                        if(count($all_missions) > 0) {
                                            foreach($all_missions as $row) {
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlentities($row->ReferenceNumber); ?></strong></td>
                                            <td>
                                                <?php if($row->UserID == $uid && $row->SubmittedBy == $uid) { ?>
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-user"></i> Pour moi
                                                    </span>
                                                <?php } elseif($row->UserID == $uid) { ?>
                                                    <span class="badge badge-info">
                                                        <i class="fas fa-gift"></i> Reçue
                                                    </span>
                                                <?php } else { ?>
                                                    <span class="badge badge-primary">
                                                        <i class="fas fa-paper-plane"></i> Créée
                                                    </span>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <?php if($row->UserID == $uid) { ?>
                                                    <strong class="text-success">Moi-même</strong>
                                                <?php } else { ?>
                                                    <?php echo htmlentities($row->MissionNom . ' ' . $row->MissionPrenom); ?>
                                                    <br><small class="text-muted"><?php echo htmlentities($row->MissionFonction); ?></small>
                                                <?php } ?>
                                            </td>
                                            <td><?php echo htmlentities($row->Destinations); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($row->DateDepart)); ?></td>
                                            <td>
                                                <?php if($row->Status == 'en_attente') { ?>
                                                    <span class="badge badge-warning">En attente</span>
                                                <?php } elseif($row->Status == 'validee') { ?>
                                                    <span class="badge badge-success">Validée</span>
                                                <?php } elseif($row->Status == 'rejetee') { ?>
                                                    <span class="badge badge-danger">Rejetée</span>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="view-mission.php?mid=<?php echo $row->ID; ?>" 
                                                       class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if($row->Status == 'validee') { ?>
                                                    <a href="print-mission.php?mid=<?php echo $row->ID; ?>" 
                                                       class="btn btn-sm btn-success" target="_blank">
                                                        <i class="fas fa-print"></i>
                                                    </a>
                                                    <?php } ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php }
                                        } else { ?>
                                        <tr><td colspan="7" class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <h5>Aucune demande</h5>
                                            <p class="text-muted">Commencez par créer votre première demande !</p>
                                            <a href="create-mission.php" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Créer une Demande
                                            </a>
                                        </td></tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Les autres onglets sont similaires mais avec des requêtes différentes -->
                <div class="tab-pane fade" id="by-me" role="tabpanel">
                    <div class="card">
                        <div class="card-body">
                            <p class="text-center text-muted">Contenu du deuxième onglet...</p>
                        </div>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="all" role="tabpanel">
                    <div class="card">
                        <div class="card-body">
                            <p class="text-center text-muted">Contenu du troisième onglet...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Initialiser les tables
        $('#forMeTable, #byMeTable, #allTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/French.json"
            },
            "pageLength": 10
        });
        
        // Mettre à jour les compteurs
        $('#for-me-count').text(<?php echo count($for_me_missions); ?>);
        $('#by-me-count').text(<?php echo count($by_me_missions ?? []); ?>);
        $('#all-count').text(<?php echo count($all_missions ?? []); ?>);
    });
    </script>
</body>
</html>

<?php } ?>
