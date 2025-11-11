<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['GMSaid']) == 0) {
    header('location:logout.php');
} else {
    $vid = intval($_GET['viewid']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails Utilisateur - Système de Gestion des Missions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include_once('includes/sidebar.php'); ?>
    
    <div class="main-content">
        <?php include_once('includes/header.php'); ?>
        
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-user"></i> Détails de l'Utilisateur</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Accueil</a></li>
                        <li class="breadcrumb-item"><a href="manage-users.php">Utilisateurs</a></li>
                        <li class="breadcrumb-item active">Détails</li>
                    </ol>
                </nav>
            </div>
            
            <?php
            $sql = "SELECT * FROM tblusers WHERE ID=:vid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':vid', $vid, PDO::PARAM_STR);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_OBJ);
            
            if($query->rowCount() > 0) {
            ?>
            
            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-id-card"></i> Informations Personnelles</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Nom Complet:</th>
                                    <td><?php echo htmlentities($result->Nom . ' ' . $result->Prenom); ?></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td><?php echo htmlentities($result->Email); ?></td>
                                </tr>
                                <tr>
                                    <th>Téléphone:</th>
                                    <td>
                                        <?php if($result->MobileNumber) { ?>
                                            <?php echo htmlentities($result->MobileNumber); ?>
                                        <?php } else { ?>
                                            <span class="text-muted">Non renseigné</span>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Fonction:</th>
                                    <td><?php echo htmlentities($result->Fonction); ?></td>
                                </tr>
                                <tr>
                                    <th>Département:</th>
                                    <td><?php echo htmlentities($result->Departement); ?></td>
                                </tr>
                                <tr>
                                    <th>Rôle:</th>
                                    <td>
                                        <?php if($result->Role == 'admin') { ?>
                                            <span class="badge badge-danger badge-lg">
                                                <i class="fas fa-user-shield"></i> Administrateur
                                            </span>
                                        <?php } elseif($result->Role == 'chef_departement') { ?>
                                            <span class="badge badge-warning badge-lg">
                                                <i class="fas fa-user-tie"></i> Chef de Département
                                            </span>
                                        <?php } else { ?>
                                            <span class="badge badge-info badge-lg">
                                                <i class="fas fa-user"></i> Utilisateur
                                            </span>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Date d'Inscription:</th>
                                    <td><?php echo date('d/m/Y à H:i', strtotime($result->RegDate)); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <!-- Statistiques des missions -->
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Statistiques des Missions</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            // Statistiques des missions de cet utilisateur
                            $sql = "SELECT 
                                       COUNT(*) as total,
                                       COUNT(CASE WHEN Status='en_attente' THEN 1 END) as en_attente,
                                       COUNT(CASE WHEN Status='validee' THEN 1 END) as validees,
                                       COUNT(CASE WHEN Status='rejetee' THEN 1 END) as rejetees
                                    FROM tblmissions WHERE UserID=:vid";
                            $query = $dbh->prepare($sql);
                            $query->bindParam(':vid', $vid, PDO::PARAM_STR);
                            $query->execute();
                            $stats = $query->fetch(PDO::FETCH_OBJ);
                            ?>
                            
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <div class="border rounded p-3">
                                        <h4 class="text-primary"><?php echo $stats->total; ?></h4>
                                        <small class="text-muted">Total Missions</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="border rounded p-3">
                                        <h4 class="text-warning"><?php echo $stats->en_attente; ?></h4>
                                        <small class="text-muted">En Attente</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded p-3">
                                        <h4 class="text-success"><?php echo $stats->validees; ?></h4>
                                        <small class="text-muted">Validées</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded p-3">
                                        <h4 class="text-danger"><?php echo $stats->rejetees; ?></h4>
                                        <small class="text-muted">Rejetées</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6><i class="fas fa-cogs"></i> Actions</h6>
                        </div>
                        <div class="card-body">
                            <a href="edit-user.php?editid=<?php echo $result->ID; ?>" 
                               class="btn btn-warning btn-block">
                                <i class="fas fa-edit"></i> Modifier cet Utilisateur
                            </a>
                            
                            <?php if($result->Role != 'admin') { ?>
                            <a href="manage-users.php?delid=<?php echo $result->ID; ?>" 
                               class="btn btn-danger btn-block mt-2"
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                <i class="fas fa-trash"></i> Supprimer cet Utilisateur
                            </a>
                            <?php } ?>
                            
                            <a href="manage-users.php" class="btn btn-secondary btn-block mt-2">
                                <i class="fas fa-arrow-left"></i> Retour à la Liste
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Dernières missions de cet utilisateur -->
            <?php if($stats->total > 0) { ?>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-history"></i> Dernières Missions</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Référence</th>
                                            <th>Destinations</th>
                                            <th>Date Départ</th>
                                            <th>Motif</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT * FROM tblmissions WHERE UserID=:vid ORDER BY DateCreation DESC LIMIT 5";
                                        $query = $dbh->prepare($sql);
                                        $query->bindParam(':vid', $vid, PDO::PARAM_STR);
                                        $query->execute();
                                        $missions = $query->fetchAll(PDO::FETCH_OBJ);
                                        
                                        foreach($missions as $mission) {
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlentities($mission->ReferenceNumber); ?></strong></td>
                                            <td><?php echo htmlentities($mission->Destinations); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($mission->DateDepart)); ?></td>
                                            <td><?php echo htmlentities($mission->MotifDeplacement); ?></td>
                                            <td>
                                                <?php if($mission->Status == 'en_attente') { ?>
                                                    <span class="badge badge-warning">En attente</span>
                                                <?php } elseif($mission->Status == 'validee') { ?>
                                                    <span class="badge badge-success">Validée</span>
                                                <?php } else { ?>
                                                    <span class="badge badge-danger">Rejetée</span>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <a href="view-mission-detail.php?viewid=<?php echo $mission->ID; ?>" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
            
            <?php } else { ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                Utilisateur non trouvé.
            </div>
            <?php } ?>
        </div>
    </div>
    
    <?php include_once('includes/footer.php'); ?>
</body>
</html>

<?php } ?>
