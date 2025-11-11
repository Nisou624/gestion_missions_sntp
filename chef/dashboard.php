<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['GMScid']) == 0) {
    header('location:logout.php');
} else {
    // Statistiques pour le dashboard chef
    $sql = "SELECT COUNT(*) as total FROM tblmissions WHERE Status='en_attente'";
    $query = $dbh->prepare($sql);
    $query->execute();
    $enAttente = $query->fetch(PDO::FETCH_OBJ)->total;
    
    $sql = "SELECT COUNT(*) as total FROM tblmissions WHERE Status='validee' AND ValidatedBy=:cid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':cid', $_SESSION['GMScid'], PDO::PARAM_STR);
    $query->execute();
    $validees = $query->fetch(PDO::FETCH_OBJ)->total;
    
    $sql = "SELECT COUNT(*) as total FROM tblmissions WHERE Status='rejetee' AND ValidatedBy=:cid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':cid', $_SESSION['GMScid'], PDO::PARAM_STR);
    $query->execute();
    $rejetees = $query->fetch(PDO::FETCH_OBJ)->total;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Chef - Gestion des Missions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include_once('includes/sidebar.php'); ?>
    
    <div class="main-content">
        <?php include_once('includes/header.php'); ?>
        
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-tachometer-alt"></i> Tableau de Bord - Validation des Missions</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active">Accueil</li>
                    </ol>
                </nav>
            </div>
            
            <!-- Statistiques principales -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3><?php echo $enAttente; ?></h3>
                                    <p class="mb-0">Demandes en Attente</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="pending-missions.php" class="text-white">
                                Traiter maintenant <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3><?php echo $validees; ?></h3>
                                    <p class="mb-0">Missions Validées</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="validated-missions.php" class="text-white">
                                Voir les détails <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3><?php echo $rejetees; ?></h3>
                                    <p class="mb-0">Missions Rejetées</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-times-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="rejected-missions.php" class="text-white">
                                Voir les détails <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Demandes récentes en attente -->
            <?php if($enAttente > 0) { ?>
            <div class="row">
                <div class="col-12">
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-white">
                            <h5><i class="fas fa-exclamation-triangle"></i> Demandes Nécessitant Votre Attention</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Référence</th>
                                            <th>Demandeur</th>
                                            <th>Destination</th>
                                            <th>Date Départ</th>
                                            <th>Motif</th>
                                            <th>Date Demande</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT m.*, u.Nom, u.Prenom FROM tblmissions m 
                                               JOIN tblusers u ON m.UserID = u.ID 
                                               WHERE m.Status='en_attente' 
                                               ORDER BY m.DateCreation ASC LIMIT 5";
                                        $query = $dbh->prepare($sql);
                                        $query->execute();
                                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                                        
                                        if($query->rowCount() > 0) {
                                            foreach($results as $row) {
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlentities($row->ReferenceNumber); ?></strong></td>
                                            <td><?php echo htmlentities($row->Nom . ' ' . $row->Prenom); ?></td>
                                            <td><?php echo htmlentities($row->Destinations); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($row->DateDepart)); ?></td>
                                            <td><?php echo htmlentities($row->MotifDeplacement); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($row->DateCreation)); ?></td>
                                            <td>
                                                <a href="validate-mission.php?mid=<?php echo $row->ID; ?>" 
                                                   class="btn btn-sm btn-warning text-white">
                                                    <i class="fas fa-eye"></i> Examiner
                                                </a>
                                            </td>
                                        </tr>
                                        <?php }} ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <?php if($enAttente > 5) { ?>
                            <div class="text-center mt-3">
                                <a href="pending-missions.php" class="btn btn-warning">
                                    Voir toutes les demandes en attente (<?php echo $enAttente; ?>)
                                </a>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php } else { ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-success text-center">
                        <i class="fas fa-check-circle fa-2x mb-3"></i>
                        <h4>Excellent travail !</h4>
                        <p class="mb-0">Toutes les demandes de mission ont été traitées.</p>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php } ?>
