<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['GMSuid']) == 0) {
    header('location:logout.php');
} else {
    $uid = $_SESSION['GMSuid'];
    
    // Statistiques pour le dashboard utilisateur
    $sql = "SELECT COUNT(*) as total FROM tblmissions WHERE UserID=:uid AND Status='en_attente'";
    $query = $dbh->prepare($sql);
    $query->bindParam(':uid', $uid, PDO::PARAM_STR);
    $query->execute();
    $enAttente = $query->fetch(PDO::FETCH_OBJ)->total;
    
    $sql = "SELECT COUNT(*) as total FROM tblmissions WHERE UserID=:uid AND Status='validee'";
    $query = $dbh->prepare($sql);
    $query->bindParam(':uid', $uid, PDO::PARAM_STR);
    $query->execute();
    $validees = $query->fetch(PDO::FETCH_OBJ)->total;
    
    $sql = "SELECT COUNT(*) as total FROM tblmissions WHERE UserID=:uid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':uid', $uid, PDO::PARAM_STR);
    $query->execute();
    $totalMissions = $query->fetch(PDO::FETCH_OBJ)->total;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Tableau de Bord - Gestion des Missions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include_once('includes/sidebar.php'); ?>
    
    <div class="main-content">
        <?php include_once('includes/header.php'); ?>
        
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-tachometer-alt"></i> Mon Tableau de Bord</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active">Accueil</li>
                    </ol>
                </nav>
            </div>
            
            <!-- Message de bienvenue -->
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Bienvenue !</strong> Gérez vos demandes d'ordres de mission depuis cet espace personnel.
            </div>
            
            <!-- Statistiques principales -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3><?php echo $enAttente; ?></h3>
                                    <p class="mb-0">En Attente</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="my-missions.php" class="text-white">
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
                                    <p class="mb-0">Validées</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="validated-missions.php" class="text-white">
                                Imprimer PDF <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3><?php echo $totalMissions; ?></h3>
                                    <p class="mb-0">Total Missions</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-list fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="my-missions.php" class="text-white">
                                Voir toutes <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3><i class="fas fa-plus"></i></h3>
                                    <p class="mb-0">Nouvelle Demande</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-plus-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="create-mission.php" class="text-white">
                                Créer maintenant <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Mes dernières demandes -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-history"></i> Mes Dernières Demandes</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Référence</th>
                                            <th>Destinations</th>
                                            <th>Date Départ</th>
                                            <th>Date Retour</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT * FROM tblmissions WHERE UserID=:uid ORDER BY DateCreation DESC LIMIT 5";
                                        $query = $dbh->prepare($sql);
                                        $query->bindParam(':uid', $uid, PDO::PARAM_STR);
                                        $query->execute();
                                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                                        
                                        if($query->rowCount() > 0) {
                                            foreach($results as $row) {
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlentities($row->ReferenceNumber); ?></strong></td>
                                            <td><?php echo htmlentities($row->Destinations); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($row->DateDepart)); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($row->DateRetour)); ?></td>
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
                                                <div class="btn-group" role="group">
                                                    <a href="view-mission.php?mid=<?php echo $row->ID; ?>" 
                                                       class="btn btn-sm btn-info" title="Voir détails">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if($row->Status == 'validee') { ?>
                                                    <a href="print-mission.php?mid=<?php echo $row->ID; ?>" 
                                                       class="btn btn-sm btn-success" title="Imprimer PDF" target="_blank">
                                                        <i class="fas fa-print"></i>
                                                    </a>
                                                    <?php } ?>
                                                    <?php if($row->Status == 'en_attente') { ?>
                                                    <a href="edit-mission.php?mid=<?php echo $row->ID; ?>" 
                                                       class="btn btn-sm btn-warning" title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php } ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php }
                                        } else { ?>
                                        <tr>
                                            <td colspan="6" class="text-center">
                                                <div class="py-4">
                                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                                    <h5>Aucune demande de mission</h5>
                                                    <p class="text-muted">Commencez par créer votre première demande !</p>
                                                    <a href="create-mission.php" class="btn btn-primary">
                                                        <i class="fas fa-plus"></i> Créer une Demande
                                                    </a>
                                                </div>
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
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php } ?>
