<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['GMScid']) == 0) {
    header('location:logout.php');
} else {
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Missions en Attente - Système de Gestion des Missions</title>
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
                <h2><i class="fas fa-clock text-warning"></i> Demandes en Attente de Validation</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Accueil</a></li>
                        <li class="breadcrumb-item active">Missions en Attente</li>
                    </ol>
                </nav>
            </div>
            
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Demandes Nécessitant Votre Validation
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
                                    <th>Motif</th>
                                    <th>Date Demande</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT m.*, u.Nom, u.Prenom, u.Fonction as UserFonction 
                                       FROM tblmissions m 
                                       JOIN tblusers u ON m.UserID = u.ID 
                                       WHERE m.Status='en_attente' 
                                       ORDER BY m.DateCreation ASC";
                                $query = $dbh->prepare($sql);
                                $query->execute();
                                $results = $query->fetchAll(PDO::FETCH_OBJ);
                                $cnt = 1;
                                
                                if($query->rowCount() > 0) {
                                    foreach($results as $row) {
                                        // Calculer l'urgence (nombre de jours depuis la demande)
                                        $dateCreation = new DateTime($row->DateCreation);
                                        $aujourd_hui = new DateTime();
                                        $diff = $aujourd_hui->diff($dateCreation);
                                        $jours = $diff->days;
                                        
                                        $urgence_class = '';
                                        if($jours > 3) {
                                            $urgence_class = 'table-danger';
                                        } elseif($jours > 1) {
                                            $urgence_class = 'table-warning';
                                        }
                                ?>
                                <tr class="<?php echo $urgence_class; ?>">
                                    <td><?php echo $cnt; ?></td>
                                    <td>
                                        <strong><?php echo htmlentities($row->ReferenceNumber); ?></strong>
                                        <?php if($jours > 3) { ?>
                                            <br><small class="text-danger">
                                                <i class="fas fa-exclamation-circle"></i> Urgent (<?php echo $jours; ?> jours)
                                            </small>
                                        <?php } ?>
                                    </td>
                                    <td><?php echo htmlentities($row->Nom . ' ' . $row->Prenom); ?></td>
                                    <td><?php echo htmlentities($row->UserFonction); ?></td>
                                    <td><?php echo htmlentities($row->Destinations); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row->DateDepart)); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row->DateRetour)); ?></td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?php echo htmlentities($row->MotifDeplacement); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y H:i', strtotime($row->DateCreation)); ?>
                                        <br><small class="text-muted">Il y a <?php echo $jours; ?> jour(s)</small>
                                    </td>
                                    <td>
                                        <a href="validate-mission.php?mid=<?php echo $row->ID; ?>" 
                                           class="btn btn-warning btn-sm text-white">
                                            <i class="fas fa-eye"></i> Examiner
                                        </a>
                                    </td>
                                </tr>
                                <?php $cnt++; }
                                } else { ?>
                                <tr>
                                    <td colspan="10" class="text-center">
                                        <div class="py-4">
                                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                            <h5>Aucune demande en attente</h5>
                                            <p class="text-muted">Toutes les demandes de mission ont été traitées.</p>
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
            "order": [[ 8, "asc" ]] // Trier par date de création (plus ancien en premier)
        });
    });
    </script>
</body>
</html>

<?php } ?>
