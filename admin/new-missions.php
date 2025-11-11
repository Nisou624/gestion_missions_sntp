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
    <title>Nouvelles Missions - Système de Gestion des Missions</title>
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
                <h2><i class="fas fa-clock text-warning"></i> Nouvelles Demandes de Mission</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Accueil</a></li>
                        <li class="breadcrumb-item active">Nouvelles Missions</li>
                    </ol>
                </nav>
            </div>
            
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Demandes en Attente de Validation
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
                                        // Calculer l'urgence
                                        $dateCreation = new DateTime($row->DateCreation);
                                        $aujourd_hui = new DateTime();
                                        $diff = $aujourd_hui->diff($dateCreation);
                                        $jours = $diff->days;
                                        
                                        $urgence_class = '';
                                        if($jours > 5) {
                                            $urgence_class = 'table-danger';
                                        } elseif($jours > 2) {
                                            $urgence_class = 'table-warning';
                                        }
                                ?>
                                <tr class="<?php echo $urgence_class; ?>">
                                    <td><?php echo $cnt; ?></td>
                                    <td>
                                        <strong><?php echo htmlentities($row->ReferenceNumber); ?></strong>
                                        <?php if($jours > 5) { ?>
                                            <br><small class="text-danger">
                                                <i class="fas fa-exclamation-circle"></i> Très urgent (<?php echo $jours; ?> jours)
                                            </small>
                                        <?php } elseif($jours > 2) { ?>
                                            <br><small class="text-warning">
                                                <i class="fas fa-clock"></i> Urgent (<?php echo $jours; ?> jours)
                                            </small>
                                        <?php } ?>
                                    </td>
                                    <td><?php echo htmlentities($row->Nom . ' ' . $row->Prenom); ?></td>
                                    <td><?php echo htmlentities($row->UserFonction); ?></td>
                                    <td><?php echo htmlentities($row->Destinations); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row->DateDepart)); ?></td>
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
                                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                            <h5>Aucune nouvelle demande</h5>
                                            <p class="text-muted">Toutes les demandes ont été traitées.</p>
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
