<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['GMSaid']) == 0) {
    header('location:logout.php');
} else {
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toutes les Missions - Système de Gestion des Missions</title>
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
                <h2><i class="fas fa-list"></i> Toutes les Missions</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Accueil</a></li>
                        <li class="breadcrumb-item active">Toutes les Missions</li>
                    </ol>
                </nav>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Liste Complète des Demandes de Mission</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Référence</th>
                                    <th>Demandeur</th>
                                    <th>Fonction</th>
                                    <th>Destinations</th>
                                    <th>Date Départ</th>
                                    <th>Date Retour</th>
                                    <th>Statut</th>
                                    <th>Date Demande</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT m.*, u.Nom, u.Prenom, u.Fonction as UserFonction 
                                       FROM tblmissions m 
                                       JOIN tblusers u ON m.UserID = u.ID 
                                       ORDER BY m.DateCreation DESC";
                                $query = $dbh->prepare($sql);
                                $query->execute();
                                $results = $query->fetchAll(PDO::FETCH_OBJ);
                                $cnt = 1;
                                
                                if($query->rowCount() > 0) {
                                    foreach($results as $row) {
                                ?>
                                <tr>
                                    <td><?php echo htmlentities($cnt); ?></td>
                                    <td>
                                        <strong><?php echo htmlentities($row->ReferenceNumber); ?></strong>
                                    </td>
                                    <td><?php echo htmlentities($row->Nom . ' ' . $row->Prenom); ?></td>
                                    <td><?php echo htmlentities($row->UserFonction); ?></td>
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
                                        <?php } else { ?>
                                            <span class="badge badge-info">
                                                <i class="fas fa-plane"></i> En cours
                                            </span>
                                        <?php } ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row->DateCreation)); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="view-mission-detail.php?viewid=<?php echo $row->ID; ?>" 
                                               class="btn btn-sm btn-primary" title="Voir détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if($row->Status == 'validee') { ?>
                                            <a href="../includes/generate-pdf.php?mid=<?php echo $row->ID; ?>" 
                                               class="btn btn-sm btn-success" title="Générer PDF" target="_blank">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php $cnt++; }} ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include_once('includes/footer.php'); ?>
</body>
</html>

<?php } ?>
