<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
require_once('../includes/security.php');

if (strlen($_SESSION['GMSaid']) == 0) {
    header('location:logout.php');
    exit();
}

$securityManager = new SecurityManager($dbh);
if (!$securityManager->validateSession($_SESSION['GMSaid'])) {
    header('location:logout.php');
    exit();
}

// Paramètres de pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 50;
$offset = ($page - 1) * $perPage;

// Filtres
$filterAction = isset($_GET['action']) ? $_GET['action'] : '';
$filterStatus = isset($_GET['status']) ? $_GET['status'] : '';
$filterUser = isset($_GET['user']) ? $_GET['user'] : '';
$filterDateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$filterDateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Construction de la requête
$whereConditions = [];
$params = [];

if ($filterAction) {
    $whereConditions[] = "Action = :action";
    $params[':action'] = $filterAction;
}
if ($filterStatus) {
    $whereConditions[] = "Status = :status";
    $params[':status'] = $filterStatus;
}
if ($filterUser) {
    $whereConditions[] = "(Email LIKE :user OR UserID IN (SELECT ID FROM tblusers WHERE CONCAT(Nom, ' ', Prenom) LIKE :user))";
    $params[':user'] = "%$filterUser%";
}
if ($filterDateFrom) {
    $whereConditions[] = "DATE(Timestamp) >= :date_from";
    $params[':date_from'] = $filterDateFrom;
}
if ($filterDateTo) {
    $whereConditions[] = "DATE(Timestamp) <= :date_to";
    $params[':date_to'] = $filterDateTo;
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Compter le total
$sqlCount = "SELECT COUNT(*) as total FROM tblactivity_logs $whereClause";
$queryCount = $dbh->prepare($sqlCount);
foreach ($params as $key => $value) {
    $queryCount->bindValue($key, $value);
}
$queryCount->execute();
$totalLogs = $queryCount->fetch(PDO::FETCH_OBJ)->total;
$totalPages = ceil($totalLogs / $perPage);

// Récupérer les logs
$sql = "SELECT al.*, CONCAT(u.Nom, ' ', u.Prenom) as UserFullName 
        FROM tblactivity_logs al 
        LEFT JOIN tblusers u ON al.UserID = u.ID 
        $whereClause 
        ORDER BY al.Timestamp DESC 
        LIMIT :offset, :perpage";
$query = $dbh->prepare($sql);
foreach ($params as $key => $value) {
    $query->bindValue($key, $value);
}
$query->bindValue(':offset', $offset, PDO::PARAM_INT);
$query->bindValue(':perpage', $perPage, PDO::PARAM_INT);
$query->execute();
$logs = $query->fetchAll(PDO::FETCH_OBJ);

// Récupérer les actions uniques pour le filtre
$sqlActions = "SELECT DISTINCT Action FROM tblactivity_logs ORDER BY Action";
$queryActions = $dbh->prepare($sqlActions);
$queryActions->execute();
$actions = $queryActions->fetchAll(PDO::FETCH_OBJ);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs d'Activité - Système de Gestion des Missions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <style>
        .status-success { color: #28a745; }
        .status-failure { color: #dc3545; }
        .status-warning { color: #ffc107; }
        .log-details { font-size: 0.9em; color: #6c757d; }
        .filter-card { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <?php include_once('includes/header.php'); ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include_once('includes/sidebar.php'); ?>
            
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-history"></i> Logs d'Activité</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                            <i class="fas fa-print"></i> Imprimer
                        </button>
                    </div>
                </div>

                <!-- Filtres -->
                <div class="filter-card">
                    <form method="GET" action="">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Action</label>
                                <select name="action" class="form-control form-control-sm">
                                    <option value="">Toutes les actions</option>
                                    <?php foreach($actions as $action): ?>
                                        <option value="<?php echo $action->Action; ?>" <?php echo ($filterAction == $action->Action) ? 'selected' : ''; ?>>
                                            <?php echo $action->Action; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Statut</label>
                                <select name="status" class="form-control form-control-sm">
                                    <option value="">Tous les statuts</option>
                                    <option value="success" <?php echo ($filterStatus == 'success') ? 'selected' : ''; ?>>Succès</option>
                                    <option value="failure" <?php echo ($filterStatus == 'failure') ? 'selected' : ''; ?>>Échec</option>
                                    <option value="warning" <?php echo ($filterStatus == 'warning') ? 'selected' : ''; ?>>Avertissement</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Utilisateur</label>
                                <input type="text" name="user" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filterUser); ?>" placeholder="Nom ou email">
                            </div>
                            <div class="col-md-2">
                                <label>Date début</label>
                                <input type="date" name="date_from" class="form-control form-control-sm" value="<?php echo $filterDateFrom; ?>">
                            </div>
                            <div class="col-md-2">
                                <label>Date fin</label>
                                <input type="date" name="date_to" class="form-control form-control-sm" value="<?php echo $filterDateTo; ?>">
                            </div>
                            <div class="col-md-1">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-sm btn-block">
                                    <i class="fas fa-filter"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Statistiques rapides -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <strong><?php echo number_format($totalLogs); ?></strong> entrées trouvées
                        </div>
                    </div>
                </div>

                <!-- Tableau des logs -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>Date/Heure</th>
                                <th>Utilisateur</th>
                                <th>Action</th>
                                <th>Statut</th>
                                <th>IP</th>
                                <th>Détails</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($logs) > 0): ?>
                                <?php foreach($logs as $log): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y H:i:s', strtotime($log->Timestamp)); ?></td>
                                        <td>
                                            <?php if($log->UserFullName): ?>
                                                <strong><?php echo htmlspecialchars($log->UserFullName); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($log->Email); ?></small>
                                            <?php else: ?>
                                                <span class="text-muted"><?php echo htmlspecialchars($log->Email ?? 'N/A'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <code><?php echo htmlspecialchars($log->Action); ?></code>
                                            <?php if($log->EntityType): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($log->EntityType); ?> #<?php echo $log->EntityID; ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $statusIcons = [
                                                'success' => '<i class="fas fa-check-circle status-success"></i> Succès',
                                                'failure' => '<i class="fas fa-times-circle status-failure"></i> Échec',
                                                'warning' => '<i class="fas fa-exclamation-triangle status-warning"></i> Avertissement'
                                            ];
                                            echo $statusIcons[$log->Status] ?? $log->Status;
                                            ?>
                                        </td>
                                        <td><small><?php echo htmlspecialchars($log->IPAddress); ?></small></td>
                                        <td>
                                            <?php if($log->Details): ?>
                                                <small class="log-details"><?php echo htmlspecialchars($log->Details); ?></small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Aucun log trouvé</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if($totalPages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page-1; ?>&action=<?php echo $filterAction; ?>&status=<?php echo $filterStatus; ?>&user=<?php echo $filterUser; ?>&date_from=<?php echo $filterDateFrom; ?>&date_to=<?php echo $filterDateTo; ?>">Précédent</a>
                        </li>
                        
                        <?php for($i = max(1, $page-2); $i <= min($totalPages, $page+2); $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&action=<?php echo $filterAction; ?>&status=<?php echo $filterStatus; ?>&user=<?php echo $filterUser; ?>&date_from=<?php echo $filterDateFrom; ?>&date_to=<?php echo $filterDateTo; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page+1; ?>&action=<?php echo $filterAction; ?>&status=<?php echo $filterStatus; ?>&user=<?php echo $filterUser; ?>&date_from=<?php echo $filterDateFrom; ?>&date_to=<?php echo $filterDateTo; ?>">Suivant</a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

