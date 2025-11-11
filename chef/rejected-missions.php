<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['GMScid']) == 0) {
    header('location:logout.php');
} else {
    $cid = $_SESSION['GMScid'];
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
                <h2><i class="fas fa-times-circle text-danger"></i> Missions Rejetées par Moi</h2>
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
                        Missions que j'ai Rejetées
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
                                    <th>Motif Original</th>
                                    <th>Raison du Rejet</th>
                                    <th>Date de Rejet</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT m.*, u.Nom, u.Prenom, u.Fonction as UserFonction 
                                       FROM tblmissions m 
                                       JOIN tblusers u ON m.UserID = u.ID 
                                       WHERE m.Status='rejetee' AND m.ValidatedBy=:cid
                                       ORDER BY m.DateValidation DESC";
                                $query = $dbh->prepare($sql);
                                $query->bindParam(':cid', $cid, PDO::PARAM_STR);
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
                                        <span class="badge badge-info">
                                            <?php echo htmlentities($row->MotifDeplacement); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if($row->Remarque) { ?>
                                            <span class="text-danger">
                                                <i class="fas fa-comment-slash"></i>
                                                <?php echo htmlentities($row->Remarque); ?>
                                            </span>
                                        <?php } else { ?>
                                            <span class="text-muted">Aucune remarque</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <span class="text-danger">
                                            <i class="fas fa-ban"></i>
                                            <?php echo date('d/m/Y H:i', strtotime($row->DateValidation)); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="mission-detail.php?mid=<?php echo $row->ID; ?>" 
                                           class="btn btn-sm btn-info" title="Voir détails">
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
                                            <p class="text-muted">Vous n'avez rejeté aucune mission. Excellent travail !</p>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <?php if($query->rowCount() > 0) { ?>
            <div class="mt-4">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Rappel :</strong> <?php echo $query->rowCount(); ?> mission(s) ont été rejetées. 
                    Les demandeurs ont été notifiés des raisons du rejet et peuvent soumettre une nouvelle demande corrigée.
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    
    <script>
    $(document).ready(function() {
        $('.data-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/French.json"
            },
            "order": [[ 7, "desc" ]] // Trier par date de rejet
        });
    });
    </script>
</body>
</html>

<?php } ?>
