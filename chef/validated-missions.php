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
    <title>Missions Validées - Système de Gestion des Missions</title>
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
                <h2><i class="fas fa-check-circle text-success"></i> Missions Validées par Moi</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Accueil</a></li>
                        <li class="breadcrumb-item active">Missions Validées</li>
                    </ol>
                </nav>
            </div>
            
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-stamp"></i> 
                        Missions que j'ai Validées et Signées
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
                                    <th>Fonction</th>
                                    <th>Destinations</th>
                                    <th>Date Départ</th>
                                    <th>Date Retour</th>
                                    <th>Date Validation</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT m.*, u.Nom, u.Prenom, u.Fonction as UserFonction 
                                       FROM tblmissions m 
                                       JOIN tblusers u ON m.UserID = u.ID 
                                       WHERE m.Status='validee' AND m.ValidatedBy=:cid
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
                                        <strong class="text-success"><?php echo htmlentities($row->ReferenceNumber); ?></strong>
                                    </td>
                                    <td><?php echo htmlentities($row->Nom . ' ' . $row->Prenom); ?></td>
                                    <td><?php echo htmlentities($row->UserFonction); ?></td>
                                    <td><?php echo htmlentities($row->Destinations); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row->DateDepart)); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row->DateRetour)); ?></td>
                                    <td>
                                        <span class="text-success">
                                            <i class="fas fa-signature"></i>
                                            <?php echo date('d/m/Y H:i', strtotime($row->DateValidation)); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="mission-detail.php?mid=<?php echo $row->ID; ?>" 
                                               class="btn btn-sm btn-info" title="Voir détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="../includes/generate-pdf.php?mid=<?php echo $row->ID; ?>" 
                                               class="btn btn-sm btn-success" title="Télécharger PDF" target="_blank">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php $cnt++; }
                                } else { ?>
                                <tr>
                                    <td colspan="9" class="text-center">
                                        <div class="py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <h5>Aucune mission validée</h5>
                                            <p class="text-muted">Vous n'avez pas encore validé de mission.</p>
                                            <a href="pending-missions.php" class="btn btn-warning">
                                                <i class="fas fa-clock"></i> Voir les Demandes en Attente
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
            
            <?php if($query->rowCount() > 0) { ?>
            <div class="mt-4">
                <div class="alert alert-success">
                    <i class="fas fa-info-circle"></i>
                    <strong>Information :</strong> Vous avez validé et signé électroniquement <?php echo $query->rowCount(); ?> mission(s). 
                    Ces ordres de mission sont maintenant disponibles en PDF pour impression.
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
            "order": [[ 7, "desc" ]] // Trier par date de validation
        });
    });
    </script>
</body>
</html>

<?php } ?>
