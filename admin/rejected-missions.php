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
    <title>Missions Rejetées - Système de Gestion des Missions</title>
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
                <h2><i class="fas fa-times-circle text-danger"></i> Missions Rejetées</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Accueil</a></li>
                        <li class="breadcrumb-item active">Missions Rejetées</li>
                    </ol>
                </nav>
            </div>
            
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-ban"></i> 
                        Liste des Missions Rejetées
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Référence</th>
                                    <th>Demandeur</th>
                                    <th>Destinations</th>
                                    <th>Date Départ</th>
                                    <th>Motif de Rejet</th>
                                    <th>Rejeté par</th>
                                    <th>Date de Rejet</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT m.*, u.Nom, u.Prenom, 
                                               v.Nom as ValidatorNom, v.Prenom as ValidatorPrenom
                                       FROM tblmissions m 
                                       JOIN tblusers u ON m.UserID = u.ID 
                                       LEFT JOIN tblusers v ON m.ValidatedBy = v.ID
                                       WHERE m.Status='rejetee' 
                                       ORDER BY m.DateValidation DESC";
                                $query = $dbh->prepare($sql);
                                $query->execute();
                                $results = $query->fetchAll(PDO::FETCH_OBJ);
                                $cnt = 1;
                                
                                if($query->rowCount() > 0) {
                                    foreach($results as $row) {
                                ?>
                                <tr>
                                    <td><?php echo $cnt; ?></td>
                                    <td>
                                        <strong class="text-danger"><?php echo htmlentities($row->ReferenceNumber); ?></strong>
                                    </td>
                                    <td><?php echo htmlentities($row->Nom . ' ' . $row->Prenom); ?></td>
                                    <td><?php echo htmlentities($row->Destinations); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row->DateDepart)); ?></td>
                                    <td>
                                        <?php if($row->Remarque) { ?>
                                            <span class="text-danger"><?php echo htmlentities($row->Remarque); ?></span>
                                        <?php } else { ?>
                                            <span class="text-muted">Aucune remarque</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php if($row->ValidatorNom) { ?>
                                            <?php echo htmlentities($row->ValidatorNom . ' ' . $row->ValidatorPrenom); ?>
                                        <?php } else { ?>
                                            <span class="text-muted">N/A</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php if($row->DateValidation) { ?>
                                            <?php echo date('d/m/Y H:i', strtotime($row->DateValidation)); ?>
                                        <?php } else { ?>
                                            <span class="text-muted">N/A</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <a href="view-mission-detail.php?viewid=<?php echo $row->ID; ?>" 
                                           class="btn btn-sm btn-primary" title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php $cnt++; }
                                } else { ?>
                                <tr>
                                    <td colspan="9" class="text-center">
                                        <div class="py-4">
                                            <i class="fas fa-smile fa-3x text-success mb-3"></i>
                                            <h5>Aucune mission rejetée</h5>
                                            <p class="text-muted">Toutes les missions ont été acceptées !</p>
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
    
    <?php include_once('includes/footer.php'); ?>
</body>
</html>

<?php } ?>
