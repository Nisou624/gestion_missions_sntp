<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['GMSaid']) == 0) {
    header('location:logout.php');
} else {
    // Statistiques pour le dashboard
    $sql = "SELECT COUNT(*) as total FROM tblmissions WHERE Status='en_attente'";
    $query = $dbh->prepare($sql);
    $query->execute();
    $nouvelles = $query->fetch(PDO::FETCH_OBJ)->total;
    
    $sql = "SELECT COUNT(*) as total FROM tblmissions WHERE Status='validee'";
    $query = $dbh->prepare($sql);
    $query->execute();
    $validees = $query->fetch(PDO::FETCH_OBJ)->total;
    
    $sql = "SELECT COUNT(*) as total FROM tblmissions WHERE Status='rejetee'";
    $query = $dbh->prepare($sql);
    $query->execute();
    $rejetees = $query->fetch(PDO::FETCH_OBJ)->total;
    
    $sql = "SELECT COUNT(*) as total FROM tblusers";
    $query = $dbh->prepare($sql);
    $query->execute();
    $totalUsers = $query->fetch(PDO::FETCH_OBJ)->total;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Admin - Gestion des Missions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include_once('includes/sidebar.php'); ?>
    
    <div class="main-content">
        <?php include_once('includes/header.php'); ?>
        
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-tachometer-alt"></i> Tableau de Bord Administrateur</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active">Accueil</li>
                    </ol>
                </nav>
            </div>
            
            <!-- Statistiques principales -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3><?php echo $nouvelles; ?></h3>
                                    <p class="mb-0">Nouvelles Demandes</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="new-missions.php" class="text-white">
                                Voir les détails <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
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
                
                <div class="col-md-3">
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
                
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3><?php echo $totalUsers; ?></h3>
                                    <p class="mb-0">Utilisateurs</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="manage-users.php" class="text-white">
                                Gérer <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Dernières missions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-list"></i> Dernières Demandes de Mission</h5>
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
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT m.*, u.Nom, u.Prenom FROM tblmissions m 
                                               JOIN tblusers u ON m.UserID = u.ID 
                                               ORDER BY m.DateCreation DESC LIMIT 10";
                                        $query = $dbh->prepare($sql);
                                        $query->execute();
                                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                                        
                                        if($query->rowCount() > 0) {
                                            foreach($results as $row) {
                                        ?>
                                        <tr>
                                            <td><?php echo htmlentities($row->ReferenceNumber); ?></td>
                                            <td><?php echo htmlentities($row->Nom . ' ' . $row->Prenom); ?></td>
                                            <td><?php echo htmlentities($row->Destinations); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($row->DateDepart)); ?></td>
                                            <td>
                                                <?php if($row->Status == 'en_attente') { ?>
                                                    <span class="badge badge-warning">En attente</span>
                                                <?php } elseif($row->Status == 'validee') { ?>
                                                    <span class="badge badge-success">Validée</span>
                                                <?php } else { ?>
                                                    <span class="badge badge-danger">Rejetée</span>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <a href="view-mission-detail.php?viewid=<?php echo $row->ID; ?>" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php }} ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include_once('includes/footer.php'); ?>
</body>
</html>

<?php } ?>
