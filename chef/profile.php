<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['GMScid']) == 0) {
    header('location:logout.php');
} else {
    $cid = $_SESSION['GMScid'];
    
    if(isset($_POST['update'])) {
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $email = $_POST['email'];
        $fonction = $_POST['fonction'];
        $mobile = $_POST['mobile'];
        
        $sql = "UPDATE tblusers SET Nom=:nom, Prenom=:prenom, Email=:email, Fonction=:fonction, MobileNumber=:mobile WHERE ID=:cid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':nom', $nom, PDO::PARAM_STR);
        $query->bindParam(':prenom', $prenom, PDO::PARAM_STR);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':fonction', $fonction, PDO::PARAM_STR);
        $query->bindParam(':mobile', $mobile, PDO::PARAM_STR);
        $query->bindParam(':cid', $cid, PDO::PARAM_STR);
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
            $sql = "SELECT * FROM tblusers WHERE ID=:cid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':cid', $cid, PDO::PARAM_STR);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_OBJ);
            ?>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header bg-success text-white">
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
                                    <label>Rôle</label>
                                    <input type="text" class="form-control" value="Chef de Département" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label>Date d'Inscription</label>
                                    <input type="text" class="form-control" 
                                           value="<?php echo date('d/m/Y à H:i', strtotime($result->RegDate)); ?>" readonly>
                                </div>
                                
                                <div class="form-group text-center">
                                    <button type="submit" name="update" class="btn btn-success btn-lg">
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
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php } ?>
