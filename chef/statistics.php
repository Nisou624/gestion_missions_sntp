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
    <title>Mes Statistiques - Système de Gestion des Missions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include_once('includes/sidebar.php'); ?>
    
    <div class="main-content">
        <?php include_once('includes/header.php'); ?>
        
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-chart-line"></i> Mes Statistiques de Validation</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Accueil</a></li>
                        <li class="breadcrumb-item active">Statistiques</li>
                    </ol>
                </nav>
            </div>
            
            <!-- Statistiques personnelles -->
            <div class="row mb-4">
                <?php
                // Mes statistiques de validation
                $sql = "SELECT 
                           COUNT(*) as total_validees,
                           COUNT(CASE WHEN Status='validee' THEN 1 END) as validees,
                           COUNT(CASE WHEN Status='rejetee' THEN 1 END) as rejetees,
                           COUNT(CASE WHEN MONTH(DateValidation)=MONTH(CURDATE()) AND YEAR(DateValidation)=YEAR(CURDATE()) THEN 1 END) as ce_mois
                        FROM tblmissions WHERE ValidatedBy=:cid";
                $query = $dbh->prepare($sql);
                $query->bindParam(':cid', $cid, PDO::PARAM_STR);
                $query->execute();
                $my_stats = $query->fetch(PDO::FETCH_OBJ);
                
                // Missions en attente globales
                $sql = "SELECT COUNT(*) as en_attente FROM tblmissions WHERE Status='en_attente'";
                $query = $dbh->prepare($sql);
                $query->execute();
                $global_pending = $query->fetch(PDO::FETCH_OBJ)->en_attente;
                ?>
                
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3><?php echo $my_stats->total_validees; ?></h3>
                                    <p class="mb-0">Missions Traitées</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-tasks fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3><?php echo $my_stats->validees; ?></h3>
                                    <p class="mb-0">Missions Validées</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3><?php echo $my_stats->rejetees; ?></h3>
                                    <p class="mb-0">Missions Rejetées</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-times-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3><?php echo $global_pending; ?></h3>
                                    <p class="mb-0">En Attente Global</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Mon activité de validation -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-chart-pie"></i> Mon Taux de Validation</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="myValidationChart"></canvas>
                            <div class="mt-3 text-center">
                                <?php 
                                $total_traite = $my_stats->validees + $my_stats->rejetees;
                                if($total_traite > 0) {
                                    $taux_validation = ($my_stats->validees / $total_traite) * 100;
                                    echo "<p class='text-muted'>Taux de validation: <strong class='text-success'>".number_format($taux_validation, 1)."%</strong></p>";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Évolution mensuelle -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-line-chart"></i> Mon Activité (6 derniers mois)</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="monthlyActivityChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <!-- Types de missions que je valide le plus -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-tags"></i> Motifs de Missions Validées</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $sql = "SELECT MotifDeplacement, COUNT(*) as nombre 
                                   FROM tblmissions 
                                   WHERE ValidatedBy=:cid AND Status='validee'
                                   GROUP BY MotifDeplacement 
                                   ORDER BY nombre DESC 
                                   LIMIT 5";
                            $query = $dbh->prepare($sql);
                            $query->bindParam(':cid', $cid, PDO::PARAM_STR);
                            $query->execute();
                            $motifs = $query->fetchAll(PDO::FETCH_OBJ);
                            ?>
                            
                            <?php if(!empty($motifs)) { ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Motif</th>
                                            <th>Nombre</th>
                                            <th>Répartition</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($motifs as $motif) {
                                            $pourcentage = ($motif->nombre / $my_stats->validees) * 100;
                                        ?>
                                        <tr>
                                            <td><?php echo htmlentities($motif->MotifDeplacement); ?></td>
                                            <td><?php echo $motif->nombre; ?></td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-success" style="width: <?php echo $pourcentage; ?>%">
                                                        <?php echo number_format($pourcentage, 1); ?>%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php } else { ?>
                            <p class="text-muted text-center">Aucune mission validée pour le moment.</p>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                
                <!-- Temps moyen de traitement -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-stopwatch"></i> Performance de Validation</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            // Calculer le temps moyen de traitement
                            $sql = "SELECT 
                                       AVG(TIMESTAMPDIFF(HOUR, DateCreation, DateValidation)) as temps_moyen_heures,
                                       MIN(TIMESTAMPDIFF(HOUR, DateCreation, DateValidation)) as temps_min_heures,
                                       MAX(TIMESTAMPDIFF(HOUR, DateCreation, DateValidation)) as temps_max_heures
                                    FROM tblmissions 
                                    WHERE ValidatedBy=:cid AND DateValidation IS NOT NULL";
                            $query = $dbh->prepare($sql);
                            $query->bindParam(':cid', $cid, PDO::PARAM_STR);
                            $query->execute();
                            $temps_stats = $query->fetch(PDO::FETCH_OBJ);
                            ?>
                            
                            <div class="row text-center">
                                <div class="col-4">
                                    <h4 class="text-info">
                                        <?php echo $temps_stats->temps_moyen_heures ? number_format($temps_stats->temps_moyen_heures, 1) : '0'; ?>h
                                    </h4>
                                    <small class="text-muted">Temps Moyen</small>
                                </div>
                                <div class="col-4">
                                    <h4 class="text-success">
                                        <?php echo $temps_stats->temps_min_heures ? number_format($temps_stats->temps_min_heures, 1) : '0'; ?>h
                                    </h4>
                                    <small class="text-muted">Plus Rapide</small>
                                </div>
                                <div class="col-4">
                                    <h4 class="text-warning">
                                        <?php echo $temps_stats->temps_max_heures ? number_format($temps_stats->temps_max_heures, 1) : '0'; ?>h
                                    </h4>
                                    <small class="text-muted">Plus Long</small>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="text-center">
                                <h6>Évaluation de Performance</h6>
                                <?php 
                                $temps_moyen = $temps_stats->temps_moyen_heures;
                                if($temps_moyen <= 24) {
                                    echo '<span class="badge badge-success badge-lg">Excellente Réactivité</span>';
                                } elseif($temps_moyen <= 48) {
                                    echo '<span class="badge badge-warning badge-lg">Bonne Réactivité</span>';
                                } else {
                                    echo '<span class="badge badge-info badge-lg">Réactivité Standard</span>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actions rapides -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-bolt"></i> Actions Rapides</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <a href="pending-missions.php" class="btn btn-warning btn-block">
                                        <i class="fas fa-clock"></i> 
                                        Missions en Attente (<?php echo $global_pending; ?>)
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="validated-missions.php" class="btn btn-success btn-block">
                                        <i class="fas fa-check-circle"></i> 
                                        Mes Validations (<?php echo $my_stats->validees; ?>)
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="rejected-missions.php" class="btn btn-danger btn-block">
                                        <i class="fas fa-times-circle"></i> 
                                        Mes Rejets (<?php echo $my_stats->rejetees; ?>)
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <button onclick="window.print()" class="btn btn-secondary btn-block">
                                        <i class="fas fa-print"></i> 
                                        Imprimer Rapport
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Graphique de validation
    const validationCtx = document.getElementById('myValidationChart').getContext('2d');
    new Chart(validationCtx, {
        type: 'doughnut',
        data: {
            labels: ['Validées', 'Rejetées'],
            datasets: [{
                data: [<?php echo $my_stats->validees; ?>, <?php echo $my_stats->rejetees; ?>],
                backgroundColor: ['#28a745', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Graphique d'activité mensuelle
    <?php
    $monthly_data = array();
    for($i = 5; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i month"));
        $sql = "SELECT COUNT(*) as count FROM tblmissions WHERE ValidatedBy=:cid AND DATE_FORMAT(DateValidation, '%Y-%m') = '$month'";
        $query = $dbh->prepare($sql);
        $query->bindParam(':cid', $cid, PDO::PARAM_STR);
        $query->execute();
        $count = $query->fetch(PDO::FETCH_OBJ)->count;
        $monthly_data[] = $count;
    }
    ?>
    
    const activityCtx = document.getElementById('monthlyActivityChart').getContext('2d');
    new Chart(activityCtx, {
        type: 'bar',
        data: {
            labels: [
                <?php 
                for($i = 5; $i >= 0; $i--) {
                    echo "'" . date('M Y', strtotime("-$i month")) . "',";
                }
                ?>
            ],
            datasets: [{
                label: 'Missions traitées',
                data: [<?php echo implode(',', $monthly_data); ?>],
                backgroundColor: '#28a745',
                borderColor: '#1e7e34',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    </script>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php } ?>
