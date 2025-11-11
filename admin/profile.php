<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['GMSaid']) == 0) {
    header('location:logout.php');
} else {
    $aid = $_SESSION['GMSaid'];
    
    if(isset($_POST['update'])) {
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $email = $_POST['email'];
        $fonction = $_POST['fonction'];
        $mobile = $_POST['mobile'];
        
        // Vérifier si l'email existe déjà pour un autre utilisateur
        $ret = "SELECT Email FROM tblusers WHERE Email=:email AND ID!=:aid";
        $query = $dbh->prepare($ret);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':aid', $aid, PDO::PARAM_STR);
        $query->execute();
        
        if($query->rowCount() == 0) {
            $sql = "UPDATE tblusers SET Nom=:nom, Prenom=:prenom, Email=:email, Fonction=:fonction, MobileNumber=:mobile WHERE ID=:aid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':nom', $nom, PDO::PARAM_STR);
            $query->bindParam(':prenom', $prenom, PDO::PARAM_STR);
            $query->bindParam(':email', $email, PDO::PARAM_STR);
            $query->bindParam(':fonction', $fonction, PDO::PARAM_STR);
            $query->bindParam(':mobile', $mobile, PDO::PARAM_STR);
            $query->bindParam(':aid', $aid, PDO::PARAM_STR);
            $query->execute();
            
            echo '<script>alert("Profil mis à jour avec succès !");</script>';
            echo "<script>window.location.href ='profile.php'</script>";
        } else {
            echo "<script>alert('Cet email est déjà utilisé par un autre utilisateur !');</script>";
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil Admin - Système de Gestion des Missions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include_once('includes/sidebar.php'); ?>
    
    <div class="main-content">
        <?php include_once('includes/header.php'); ?>
        
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-user-shield"></i> Mon Profil Administrateur</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Accueil</a></li>
                        <li class="breadcrumb-item active">Mon Profil</li>
                    </ol>
                </nav>
            </div>
            
            <?php
            $sql = "SELECT * FROM tblusers WHERE ID=:aid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':aid', $aid, PDO::PARAM_STR);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_OBJ);
            ?>
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-id-card"></i> Mes Informations Personnelles</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nom">Nom <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="nom" name="nom" 
                                                   value="<?php echo htmlentities($result->Nom); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="prenom">Prénom <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="prenom" name="prenom" 
                                                   value="<?php echo htmlentities($result->Prenom); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Adresse Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlentities($result->Email); ?>" required>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="fonction">Fonction <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="fonction" name="fonction" 
                                                   value="<?php echo htmlentities($result->Fonction); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="mobile">Numéro de Téléphone</label>
                                            <input type="tel" class="form-control" id="mobile" name="mobile" 
                                                   value="<?php echo htmlentities($result->MobileNumber); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Département</label>
                                    <input type="text" class="form-control" 
                                           value="<?php echo htmlentities($result->Departement); ?>" readonly>
                                    <small class="form-text text-muted">En tant qu'administrateur, vous avez accès à tous les départements.</small>
                                </div>
                                
                                <div class="form-group">
                                    <label>Rôle</label>
                                    <div class="form-control-plaintext">
                                        <span class="badge badge-danger badge-lg">
                                            <i class="fas fa-user-shield"></i> Administrateur Système
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Date d'Inscription</label>
                                    <input type="text" class="form-control" 
                                           value="<?php echo date('d/m/Y à H:i', strtotime($result->RegDate)); ?>" readonly>
                                </div>
                                
                                <div class="form-group text-center">
                                    <button type="submit" name="update" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save"></i> Mettre à Jour mon Profil
                                    </button>
                                    <a href="change-password.php" class="btn btn-warning btn-lg ml-2">
                                        <i class="fas fa-key"></i> Changer mon Mot de Passe
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- Statistiques admin -->
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h6><i class="fas fa-chart-pie"></i> Statistiques Globales</h6>
                        </div>
                        <div class="card-body">
                            <?php
                            // Statistiques globales du système
                            $sql = "SELECT 
                                       COUNT(*) as total_missions,
                                       COUNT(CASE WHEN Status='en_attente' THEN 1 END) as en_attente,
                                       COUNT(CASE WHEN Status='validee' THEN 1 END) as validees,
                                       COUNT(CASE WHEN Status='rejetee' THEN 1 END) as rejetees
                                    FROM tblmissions";
                            $query = $dbh->prepare($sql);
                            $query->execute();
                            $stats = $query->fetch(PDO::FETCH_OBJ);
                            
                            $sql = "SELECT COUNT(*) as total_users FROM tblusers";
                            $query = $dbh->prepare($sql);
                            $query->execute();
                            $total_users = $query->fetch(PDO::FETCH_OBJ)->total_users;
                            ?>
                            
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <div class="border rounded p-2">
                                        <h4 class="text-primary"><?php echo $stats->total_missions; ?></h4>
                                        <small class="text-muted">Total Missions</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="border rounded p-2">
                                        <h4 class="text-info"><?php echo $total_users; ?></h4>
                                        <small class="text-muted">Utilisateurs</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded p-2">
                                        <h4 class="text-success"><?php echo $stats->validees; ?></h4>
                                        <small class="text-muted">Validées</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded p-2">
                                        <h4 class="text-warning"><?php echo $stats->en_attente; ?></h4>
                                        <small class="text-muted">En Attente</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Privilèges administrateur -->
                    <div class="card mt-4">
                        <div class="card-header bg-danger text-white">
                            <h6><i class="fas fa-shield-alt"></i> Privilèges Administrateur</h6>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-users text-primary"></i> Gestion Utilisateurs</span>
                                    <span class="badge badge-success">Actif</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-list text-info"></i> Toutes les Missions</span>
                                    <span class="badge badge-success">Actif</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-chart-bar text-warning"></i> Rapports Complets</span>
                                    <span class="badge badge-success">Actif</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-cogs text-secondary"></i> Configuration Système</span>
                                    <span class="badge badge-success">Actif</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sécurité du compte -->
                    <div class="card mt-4">
                        <div class="card-header bg-warning text-dark">
                            <h6><i class="fas fa-lock"></i> Sécurité du Compte</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">
                                <small>
                                    <strong>Dernière connexion:</strong><br>
                                    Aujourd'hui - <?php echo date('H:i'); ?>
                                </small>
                            </p>
                            
                            <p class="text-muted">
                                <small>
                                    <strong>Statut du compte:</strong><br>
                                    <span class="text-success">
                                        <i class="fas fa-shield-check"></i> Sécurisé
                                    </span>
                                </small>
                            </p>
                            
                            <div class="alert alert-warning alert-sm">
                                <small>
                                    <i class="fas fa-exclamation-triangle"></i>
                                    En tant qu'administrateur, changez régulièrement votre mot de passe.
                                </small>
                            </div>
                            
                            <a href="change-password.php" class="btn btn-outline-warning btn-sm btn-block">
                                <i class="fas fa-key"></i> Changer le Mot de Passe
                            </a>
                        </div>
                    </div>
                    
                    <!-- Actions rapides -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6><i class="fas fa-bolt"></i> Actions Rapides</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 mb-2">
                                    <a href="manage-users.php" class="btn btn-outline-primary btn-sm btn-block">
                                        <i class="fas fa-users"></i> Gérer Utilisateurs
                                    </a>
                                </div>
                                <div class="col-12 mb-2">
                                    <a href="all-missions.php" class="btn btn-outline-info btn-sm btn-block">
                                        <i class="fas fa-list"></i> Toutes les Missions
                                    </a>
                                </div>
                                <div class="col-12">
                                    <a href="reports.php" class="btn btn-outline-success btn-sm btn-block">
                                        <i class="fas fa-chart-bar"></i> Voir Rapports
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include_once('includes/footer.php'); ?>
</body>
</html>

<?php } ?>
