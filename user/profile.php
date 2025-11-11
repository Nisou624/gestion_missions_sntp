<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['GMSuid']) == 0) {
    header('location:logout.php');
} else {
    $uid = $_SESSION['GMSuid'];
    
    if(isset($_POST['update'])) {
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $email = $_POST['email'];
        $fonction = $_POST['fonction'];
        $mobile = $_POST['mobile'];
        
        $sql = "UPDATE tblusers SET Nom=:nom, Prenom=:prenom, Email=:email, Fonction=:fonction, MobileNumber=:mobile WHERE ID=:uid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':nom', $nom, PDO::PARAM_STR);
        $query->bindParam(':prenom', $prenom, PDO::PARAM_STR);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':fonction', $fonction, PDO::PARAM_STR);
        $query->bindParam(':mobile', $mobile, PDO::PARAM_STR);
        $query->bindParam(':uid', $uid, PDO::PARAM_STR);
        $query->execute();
        
        echo '<script>alert("Profil mis à jour avec succès !");</script>';
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Système de Gestion des Missions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include_once('includes/sidebar.php'); ?>
    
    <div class="main-content">
        <?php include_once('includes/header.php'); ?>
        
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-user-edit"></i> Mon Profil</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Accueil</a></li>
                        <li class="breadcrumb-item active">Mon Profil</li>
                    </ol>
                </nav>
            </div>
            
            <?php
            $sql = "SELECT * FROM tblusers WHERE ID=:uid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':uid', $uid, PDO::PARAM_STR);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_OBJ);
            ?>
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header bg-info text-white">
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
                                    <small class="form-text text-muted">Contactez l'administrateur pour modifier votre département.</small>
                                </div>
                                
                                <div class="form-group">
                                    <label>Date d'Inscription</label>
                                    <input type="text" class="form-control" 
                                           value="<?php echo date('d/m/Y à H:i', strtotime($result->RegDate)); ?>" readonly>
                                </div>
                                
                                <div class="form-group text-center">
                                    <button type="submit" name="update" class="btn btn-info btn-lg">
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
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h6><i class="fas fa-chart-pie"></i> Mes Statistiques</h6>
                        </div>
                        <div class="card-body">
                            <?php
                            // Statistiques de l'utilisateur
                            $sql = "SELECT 
                                       COUNT(*) as total,
                                       COUNT(CASE WHEN Status='en_attente' THEN 1 END) as en_attente,
                                       COUNT(CASE WHEN Status='validee' THEN 1 END) as validees,
                                       COUNT(CASE WHEN Status='rejetee' THEN 1 END) as rejetees
                                    FROM tblmissions WHERE UserID=:uid";
                            $query = $dbh->prepare($sql);
                            $query->bindParam(':uid', $uid, PDO::PARAM_STR);
                            $query->execute();
                            $stats = $query->fetch(PDO::FETCH_OBJ);
                            ?>
                            
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <div class="border rounded p-2">
                                        <h4 class="text-primary"><?php echo $stats->total; ?></h4>
                                        <small class="text-muted">Total Missions</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="border rounded p-2">
                                        <h4 class="text-warning"><?php echo $stats->en_attente; ?></h4>
                                        <small class="text-muted">En Attente</small>
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
                                        <h4 class="text-danger"><?php echo $stats->rejetees; ?></h4>
                                        <small class="text-muted">Rejetées</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6><i class="fas fa-shield-alt"></i> Sécurité du Compte</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Dernière connexion: <br><strong>Aujourd'hui</strong></p>
                            <a href="change-password.php" class="btn btn-outline-warning btn-sm">
                                <i class="fas fa-key"></i> Changer le Mot de Passe
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php } ?>
