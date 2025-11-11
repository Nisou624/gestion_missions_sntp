<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['GMSuid']) == 0) {
    header('location:logout.php');
} else {
    $uid = $_SESSION['GMSuid'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Missions Validées - Système de Gestion des Missions</title>
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
                <h2><i class="fas fa-check-circle text-success"></i> Mes Missions Validées</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Accueil</a></li>
                        <li class="breadcrumb-item active">Missions Validées</li>
                    </ol>
                </nav>
            </div>
            
            <div class="alert alert-success">
                <i class="fas fa-info-circle"></i>
                <strong>Information :</strong> Vos missions validées peuvent être imprimées sous forme d'ordre de mission officiel.
            </div>
            
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-stamp"></i> 
                        Mes Ordres de Mission Prêts à Imprimer
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Référence</th>
                                    <th>Destinations</th>
                                    <th>Date Départ</th>
                                    <th>Date Retour</th>
                                    <th>Motif</th>
                                    <th>Date Validation</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT * FROM tblmissions WHERE UserID=:uid AND Status='validee' ORDER BY DateValidation DESC";
                                $query = $dbh->prepare($sql);
                                $query->bindParam(':uid', $uid, PDO::PARAM_STR);
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
                                    <td><?php echo htmlentities($row->Destinations); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row->DateDepart)); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row->DateRetour)); ?></td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?php echo htmlentities($row->MotifDeplacement); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-success">
                                            <i class="fas fa-signature"></i>
                                            <?php echo date('d/m/Y H:i', strtotime($row->DateValidation)); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="view-mission.php?mid=<?php echo $row->ID; ?>" 
                                               class="btn btn-sm btn-info" title="Voir détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="print-mission.php?mid=<?php echo $row->ID; ?>" 
                                               class="btn btn-sm btn-success" title="Imprimer PDF" target="_blank">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            <a href="print-mission.php?mid=<?php echo $row->ID; ?>&download=1" 
                                               class="btn btn-sm btn-primary" title="Télécharger PDF">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php $cnt++; }
                                } else { ?>
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <div class="py-5">
                                            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                                            <h4>Aucune mission validée</h4>
                                            <p class="text-muted">Vos demandes de mission n'ont pas encore été validées.</p>
                                            <a href="my-missions.php" class="btn btn-warning">
                                                <i class="fas fa-clock"></i> Voir Mes Demandes en Cours
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
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-info"></i> Instructions d'Impression</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">Comment imprimer votre ordre de mission :</h6>
                                <ol>
                                    <li>Cliquez sur le bouton <span class="badge badge-success"><i class="fas fa-print"></i></span> pour visualiser le PDF</li>
                                    <li>Utilisez Ctrl+P ou le menu Imprimer de votre navigateur</li>
                                    <li>Sélectionnez votre imprimante</li>
                                    <li>Imprimez sur papier A4 blanc</li>
                                </ol>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-info">Informations importantes :</h6>
                                <ul>
                                    <li>L'ordre de mission est signé électroniquement</li>
                                    <li>Le document PDF est valide pour vos déplacements</li>
                                    <li>Gardez une copie pour vos archives personnelles</li>
                                    <li>En cas de problème, contactez votre chef de département</li>
                                </ul>
                            </div>
                        </div>
                    </div>
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
            "order": [[ 6, "desc" ]] // Trier par date de validation
        });
    });
    </script>
</body>
</html>

<?php } ?>
