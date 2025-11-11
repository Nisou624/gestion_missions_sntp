<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['GMSaid']) == 0) {
    header('location:logout.php');
} else {
    // Supprimer un utilisateur
    if(isset($_GET['delid'])) {
        $rid = intval($_GET['delid']);
        $sql = "DELETE FROM tblusers WHERE ID=:rid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':rid',$rid,PDO::PARAM_STR);
        $query->execute();
        echo "<script>alert('Utilisateur supprimé avec succès');</script>";
        echo "<script>window.location.href = 'manage-users.php'</script>";
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - Système de Gestion des Missions</title>
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
                <h2><i class="fas fa-users"></i> Gestion des Utilisateurs</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Accueil</a></li>
                        <li class="breadcrumb-item active">Utilisateurs</li>
                    </ol>
                </nav>
            </div>
            
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Liste des Utilisateurs</h5>
                    <a href="add-user.php" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Ajouter Utilisateur
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nom Complet</th>
                                    <th>Email</th>
                                    <th>Fonction</th>
                                    <th>Département</th>
                                    <th>Rôle</th>
                                    <th>Date Inscription</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT * FROM tblusers ORDER BY RegDate DESC";
                                $query = $dbh->prepare($sql);
                                $query->execute();
                                $results = $query->fetchAll(PDO::FETCH_OBJ);
                                $cnt = 1;
                                
                                if($query->rowCount() > 0) {
                                    foreach($results as $row) {
                                ?>
                                <tr>
                                    <td><?php echo htmlentities($cnt); ?></td>
                                    <td>
                                        <?php echo htmlentities($row->Nom . ' ' . $row->Prenom); ?>
                                    </td>
                                    <td><?php echo htmlentities($row->Email); ?></td>
                                    <td><?php echo htmlentities($row->Fonction); ?></td>
                                    <td><?php echo htmlentities($row->Departement); ?></td>
                                    <td>
                                        <?php if($row->Role == 'admin') { ?>
                                            <span class="badge badge-danger">Administrateur</span>
                                        <?php } elseif($row->Role == 'chef_departement') { ?>
                                            <span class="badge badge-warning">Chef de Département</span>
                                        <?php } else { ?>
                                            <span class="badge badge-info">Utilisateur</span>
                                        <?php } ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row->RegDate)); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="edit-user.php?editid=<?php echo $row->ID; ?>" 
                                               class="btn btn-sm btn-primary" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="view-user.php?viewid=<?php echo $row->ID; ?>" 
                                               class="btn btn-sm btn-success" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if($row->Role != 'admin') { ?>
                                            <a href="manage-users.php?delid=<?php echo $row->ID; ?>" 
                                               class="btn btn-sm btn-danger" title="Supprimer"
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php $cnt++; }} ?>
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
