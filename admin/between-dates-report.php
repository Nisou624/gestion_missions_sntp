<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['GMSaid']) == 0) {
    header('location:logout.php');
} else {
    
    $start_date = '';
    $end_date = '';
    $results = array();
    
    if(isset($_POST['search'])) {
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        
        $sql = "SELECT m.*, u.Nom, u.Prenom, u.Fonction as UserFonction,
                       v.Nom as ValidatorNom, v.Prenom as ValidatorPrenom
                FROM tblmissions m 
                JOIN tblusers u ON m.UserID = u.ID 
                LEFT JOIN tblusers v ON m.ValidatedBy = v.ID
                WHERE DATE(m.DateCreation) BETWEEN :start_date AND :end_date
                ORDER BY m.DateCreation DESC";
        $query = $dbh->prepare($sql);
        $query->bindParam(':start_date', $start_date, PDO::PARAM_STR);
        $query->bindParam(':end_date', $end_date, PDO::PARAM_STR);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_OBJ);
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport par Période - Système de Gestion des Missions</title>
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
                <h2><i class="fas fa-calendar-alt"></i> Rapport par Période</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Accueil</a></li>
                        <li class="breadcrumb-item"><a href="reports.php">Rapports</a></li>
                        <li class="breadcrumb-item active">Rapport par Période</li>
                    </ol>
                </nav>
            </div>
            
            <!-- Formulaire de recherche -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-search"></i> Filtrer par Période</h5>
                </div>
                <div class="card-body">
                    <form method="POST" class="form-inline">
                        <div class="form-group mr-3">
                            <label for="start_date" class="mr-2">Date de Début:</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="<?php echo $start_date; ?>" required>
                        </div>
                        <div class="form-group mr-3">
                            <label for="end_date" class="mr-2">Date de Fin:</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="<?php echo $end_date; ?>" required>
                        </div>
                        <button type="submit" name="search" class="btn btn-primary">
                            <i class="fas fa-search"></i> Rechercher
                        </button>
                        <?php if(!empty($results)) { ?>
                        <button type="button" onclick="exportResults()" class="btn btn-success ml-2">
                            <i class="fas fa-download"></i> Export CSV
                        </button>
                        <button type="button" onclick="window.print()" class="btn btn-secondary ml-2">
                            <i class="fas fa-print"></i> Imprimer
                        </button>
                        <?php } ?>
                    </form>
                </div>
            </div>
            
            <?php if(isset($_POST['search'])) { ?>
            
            <?php if(!empty($results)) { ?>
            <!-- Statistiques de la période -->
            <div class="row mb-4">
                <?php
                $total = count($results);
                $validees = count(array_filter($results, function($r) { return $r->Status == 'validee'; }));
                $en_attente = count(array_filter($results, function($r) { return $r->Status == 'en_attente'; }));
                $rejetees = count(array_filter($results, function($r) { return $r->Status == 'rejetee'; }));
                ?>
                
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h3><?php echo $total; ?></h3>
                            <p class="mb-0">Total Missions</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3><?php echo $validees; ?></h3>
                            <p class="mb-0">Validées</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h3><?php echo $en_attente; ?></h3>
                            <p class="mb-0">En Attente</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h3><?php echo $rejetees; ?></h3>
                            <p class="mb-0">Rejetées</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Résultats -->
            <div class="card">
                <div class="card-header">
                    <h5>
                        <i class="fas fa-list"></i> 
                        Missions du <?php echo date('d/m/Y', strtotime($start_date)); ?> 
                        au <?php echo date('d/m/Y', strtotime($end_date)); ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped data-table" id="resultsTable">
                            <thead>
                                <tr>
                                    <th>Référence</th>
                                    <th>Demandeur</th>
                                    <th>Destinations</th>
                                    <th>Date Départ</th>
                                    <th>Motif</th>
                                    <th>Statut</th>
                                    <th>Validé par</th>
                                    <th>Date Création</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($results as $row) { ?>
                                <tr>
                                    <td><?php echo htmlentities($row->ReferenceNumber); ?></td>
                                    <td><?php echo htmlentities($row->Nom . ' ' . $row->Prenom); ?></td>
                                    <td><?php echo htmlentities($row->Destinations); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row->DateDepart)); ?></td>
                                    <td><?php echo htmlentities($row->MotifDeplacement); ?></td>
                                    <td>
                                        <?php if($row->Status == 'en_attente') { ?>
                                            <span class="badge badge-warning">En attente</span>
                                        <?php } elseif($row->Status == 'validee') { ?>
                                            <span class="badge badge-success">Validée</span>
                                        <?php } else { ?>
                                            <span class="badge badge-danger">Rejetée</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php if($row->ValidatorNom) { ?>
                                            <?php echo htmlentities($row->ValidatorNom . ' ' . $row->ValidatorPrenom); ?>
                                        <?php } else { ?>
                                            <span class="text-muted">-</span>
                                        <?php } ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row->DateCreation)); ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <?php } else { ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                Aucune mission trouvée pour la période sélectionnée.
            </div>
            <?php } ?>
            
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
            }
        });
    });
    
    function exportResults() {
        // Export CSV simple
        var csv = [];
        var rows = document.querySelectorAll("#resultsTable tr");
        
        for (var i = 0; i < rows.length; i++) {
            var row = [], cols = rows[i].querySelectorAll("td, th");
            
            for (var j = 0; j < cols.length; j++) {
                var cellText = cols[j].innerText.replace(/"/g, '""');
                row.push('"' + cellText + '"');
            }
            
            csv.push(row.join(","));
        }
        
        downloadCSV(csv.join("\n"), 'rapport_missions_<?php echo $start_date; ?>_<?php echo $end_date; ?>.csv');
    }
    
    function downloadCSV(csv, filename) {
        var csvFile = new Blob([csv], {type: "text/csv"});
        var downloadLink = document.createElement("a");
        
        downloadLink.download = filename;
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = "none";
        
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }
    </script>
    
    <?php include_once('includes/footer.php'); ?>
</body>
</html>

<?php } ?>
