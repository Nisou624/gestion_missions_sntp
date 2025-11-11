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
    <title>Rapports et Statistiques - Système de Gestion des Missions</title>
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
                <h2><i class="fas fa-chart-bar"></i> Rapports et Statistiques</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Accueil</a></li>
                        <li class="breadcrumb-item active">Rapports</li>
                    </ol>
                </nav>
            </div>
            
            <!-- Statistiques générales -->
            <div class="row mb-4">
                <?php
                // Statistiques générales
                $sql = "SELECT 
                           COUNT(*) as total_missions,
                           COUNT(CASE WHEN Status='en_attente' THEN 1 END) as en_attente,
                           COUNT(CASE WHEN Status='validee' THEN 1 END) as validees,
                           COUNT(CASE WHEN Status='rejetee' THEN 1 END) as rejetees,
                           COUNT(CASE WHEN MONTH(DateCreation)=MONTH(CURDATE()) AND YEAR(DateCreation)=YEAR(CURDATE()) THEN 1 END) as ce_mois
                        FROM tblmissions";
                $query = $dbh->prepare($sql);
                $query->execute();
                $stats = $query->fetch(PDO::FETCH_OBJ);
                
                $sql = "SELECT COUNT(*) as total_users FROM tblusers";
                $query = $dbh->prepare($sql);
                $query->execute();
                $total_users = $query->fetch(PDO::FETCH_OBJ)->total_users;
                ?>
                
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h3><?php echo $stats->total_missions; ?></h3>
                            <p class="mb-0">Total Missions</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h3><?php echo $stats->validees; ?></h3>
                            <p class="mb-0">Missions Validées</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h3><?php echo $stats->en_attente; ?></h3>
                            <p class="mb-0">En Attente</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h3><?php echo $stats->ce_mois; ?></h3>
                            <p class="mb-0">Ce Mois</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Graphique des missions par statut -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-pie-chart"></i> Répartition des Missions par Statut</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Missions par mois -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-line-chart"></i> Évolution des Missions (6 derniers mois)</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="monthlyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <!-- Top destinations -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-map-marker-alt"></i> Destinations les Plus Fréquentes</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $sql = "SELECT Destinations, COUNT(*) as nombre 
                                   FROM tblmissions 
                                   GROUP BY Destinations 
                                   ORDER BY nombre DESC 
                                   LIMIT 10";
                            $query = $dbh->prepare($sql);
                            $query->execute();
                            $destinations = $query->fetchAll(PDO::FETCH_OBJ);
                            ?>
                            
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Destination</th>
                                            <th>Nombre</th>
                                            <th>%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($destinations as $dest) {
                                            $pourcentage = ($dest->nombre / $stats->total_missions) * 100;
                                        ?>
                                        <tr>
                                            <td><?php echo htmlentities($dest->Destinations); ?></td>
                                            <td><?php echo $dest->nombre; ?></td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar" style="width: <?php echo $pourcentage; ?>%">
                                                        <?php echo number_format($pourcentage, 1); ?>%
                                                    </div>
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
                
                <!-- Utilisateurs les plus actifs -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-users"></i> Utilisateurs les Plus Actifs</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $sql = "SELECT u.Nom, u.Prenom, u.Fonction, COUNT(m.ID) as nombre_missions
                                   FROM tblusers u 
                                   LEFT JOIN tblmissions m ON u.ID = m.UserID
                                   WHERE u.Role = 'user'
                                   GROUP BY u.ID 
                                   ORDER BY nombre_missions DESC 
                                   LIMIT 10";
                            $query = $dbh->prepare($sql);
                            $query->execute();
                            $users_actifs = $query->fetchAll(PDO::FETCH_OBJ);
                            ?>
                            
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Utilisateur</th>
                                            <th>Fonction</th>
                                            <th>Missions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($users_actifs as $user) { ?>
                                        <tr>
                                            <td><?php echo htmlentities($user->Nom . ' ' . $user->Prenom); ?></td>
                                            <td><?php echo htmlentities($user->Fonction); ?></td>
                                            <td>
                                                <span class="badge badge-primary"><?php echo $user->nombre_missions; ?></span>
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
            
            <!-- Actions rapides -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-download"></i> Actions et Exports</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <a href="between-dates-report.php" class="btn btn-info btn-block">
                                        <i class="fas fa-calendar-alt"></i> Rapport par Période
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <button onclick="exportData('missions')" class="btn btn-success btn-block">
                                        <i class="fas fa-file-excel"></i> Export Missions CSV
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button onclick="exportData('users')" class="btn btn-primary btn-block">
                                        <i class="fas fa-file-csv"></i> Export Utilisateurs CSV
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button onclick="window.print()" class="btn btn-secondary btn-block">
                                        <i class="fas fa-print"></i> Imprimer ce Rapport
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
    // Graphique en secteurs pour les statuts
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'pie',
        data: {
            labels: ['Validées', 'En Attente', 'Rejetées'],
            datasets: [{
                data: [<?php echo $stats->validees; ?>, <?php echo $stats->en_attente; ?>, <?php echo $stats->rejetees; ?>],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545']
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
    
    // Graphique linéaire pour les missions par mois
    <?php
    $months_data = array();
    for($i = 5; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i month"));
        $sql = "SELECT COUNT(*) as count FROM tblmissions WHERE DATE_FORMAT(DateCreation, '%Y-%m') = '$month'";
        $query = $dbh->prepare($sql);
        $query->execute();
        $count = $query->fetch(PDO::FETCH_OBJ)->count;
        $months_data[] = $count;
    }
    ?>
    
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: [
                <?php 
                for($i = 5; $i >= 0; $i--) {
                    echo "'" . date('M Y', strtotime("-$i month")) . "',";
                }
                ?>
            ],
            datasets: [{
                label: 'Nombre de missions',
                data: [<?php echo implode(',', $months_data); ?>],
                borderColor: '#007bff',
                backgroundColor: 'rgba(0,123,255,0.1)',
                fill: true
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
    
    // Fonction d'export
    function exportData(type) {
        if(type === 'missions') {
            window.open('export-missions.php', '_blank');
        } else if(type === 'users') {
            window.open('export-users.php', '_blank');
        }
    }
    </script>
    
    <?php include_once('includes/footer.php'); ?>
</body>
</html>

<?php } ?>
