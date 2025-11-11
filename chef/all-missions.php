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
            
            <!-- Filtres rapides -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary filter-btn" data-filter="all">
                                    <i class="fas fa-list"></i> Toutes
                                </button>
                                <button type="button" class="btn btn-outline-warning filter-btn" data-filter="en_attente">
                                    <i class="fas fa-clock"></i> En Attente
                                </button>
                                <button type="button" class="btn btn-outline-success filter-btn" data-filter="validee">
                                    <i class="fas fa-check-circle"></i> Validées
                                </button>
                                <button type="button" class="btn btn-outline-danger filter-btn" data-filter="rejetee">
                                    <i class="fas fa-times-circle"></i> Rejetées
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-database"></i> 
                        Liste Complète des Missions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped data-table" id="missionsTable">
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
                                    <th>Statut</th>
                                    <th>Date Création</th>
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
                                <tr data-status="<?php echo $row->Status; ?>">
                                    <td><?php echo $cnt; ?></td>
                                    <td>
                                        <strong><?php echo htmlentities($row->ReferenceNumber); ?></strong>
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
                                            <span class="badge badge-primary">
                                                <i class="fas fa-plane"></i> En cours
                                            </span>
                                        <?php } ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row->DateCreation)); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <?php if($row->Status == 'en_attente') { ?>
                                                <a href="validate-mission.php?mid=<?php echo $row->ID; ?>" 
                                                   class="btn btn-sm btn-warning text-white" title="Valider">
                                                    <i class="fas fa-signature"></i>
                                                </a>
                                            <?php } else { ?>
                                                <a href="mission-detail.php?mid=<?php echo $row->ID; ?>" 
                                                   class="btn btn-sm btn-info" title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            <?php } ?>
                                            
                                            <?php if($row->Status == 'validee') { ?>
                                            <a href="../includes/generate-pdf.php?mid=<?php echo $row->ID; ?>" 
                                               class="btn btn-sm btn-success" title="PDF" target="_blank">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php $cnt++; }
                                } else { ?>
                                <tr>
                                    <td colspan="11" class="text-center">
                                        <div class="py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <h5>Aucune mission</h5>
                                            <p class="text-muted">Aucune mission n'a encore été créée.</p>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Résumé statistique -->
            <?php if($query->rowCount() > 0) { ?>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6><i class="fas fa-chart-pie"></i> Résumé des Missions</h6>
                        </div>
                        <div class="card-body">
                            <?php
                            $total = $query->rowCount();
                            $en_attente = count(array_filter($results, function($r) { return $r->Status == 'en_attente'; }));
                            $validees = count(array_filter($results, function($r) { return $r->Status == 'validee'; }));
                            $rejetees = count(array_filter($results, function($r) { return $r->Status == 'rejetee'; }));
                            ?>
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <h4 class="text-primary"><?php echo $total; ?></h4>
                                    <small class="text-muted">Total Missions</small>
                                </div>
                                <div class="col-md-3">
                                    <h4 class="text-warning"><?php echo $en_attente; ?></h4>
                                    <small class="text-muted">En Attente</small>
                                </div>
                                <div class="col-md-3">
                                    <h4 class="text-success"><?php echo $validees; ?></h4>
                                    <small class="text-muted">Validées</small>
                                </div>
                                <div class="col-md-3">
                                    <h4 class="text-danger"><?php echo $rejetees; ?></h4>
                                    <small class="text-muted">Rejetées</small>
                                </div>
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
        var table = $('.data-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/French.json"
            },
            "pageLength": 25,
            "order": [[ 9, "desc" ]] // Trier par date de création
        });
        
        // Filtres par statut
        $('.filter-btn').click(function() {
            var filter = $(this).data('filter');
            
            // Réinitialiser les boutons
            $('.filter-btn').removeClass('btn-primary btn-warning btn-success btn-danger')
                           .addClass('btn-outline-primary');
            
            if(filter === 'all') {
                $(this).removeClass('btn-outline-primary').addClass('btn-primary');
                table.column(8).search('').draw(); // Colonne statut
            } else {
                // Appliquer le style approprié
                if(filter === 'en_attente') {
                    $(this).removeClass('btn-outline-warning').addClass('btn-warning');
                } else if(filter === 'validee') {
                    $(this).removeClass('btn-outline-success').addClass('btn-success');
                } else if(filter === 'rejetee') {
                    $(this).removeClass('btn-outline-danger').addClass('btn-danger');
                }
                
                table.column(8).search(filter === 'en_attente' ? 'En attente' : 
                                     filter === 'validee' ? 'Validée' : 
                                     'Rejetée').draw();
            }
        });
        
        // Activer le filtre "Toutes" par défaut
        $('.filter-btn[data-filter="all"]').click();
    });
    </script>
</body>
</html>

<?php } ?>
