<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['GMScid']) == 0) {
    header('location:logout.php');
} else {
    if(isset($_POST['submit'])) {
        $cid = $_SESSION['GMScid'];
        $currentpassword = $_POST['currentpassword'];
        $newpassword = $_POST['newpassword'];
        
        $sql = "SELECT Password FROM tblusers WHERE ID=:cid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':cid', $cid, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_OBJ);
        
        if(password_verify($currentpassword, $result->Password)) {
            $new_hash = password_hash($newpassword, PASSWORD_DEFAULT);
            $con = "UPDATE tblusers SET Password=:newpassword WHERE ID=:cid";
            $chngpwd1 = $dbh->prepare($con);
            $chngpwd1->bindParam(':cid', $cid, PDO::PARAM_STR);
            $chngpwd1->bindParam(':newpassword', $new_hash, PDO::PARAM_STR);
            $chngpwd1->execute();
            
            echo '<script>alert("Mot de passe changé avec succès !");</script>';
        } else {
            echo '<script>alert("Mot de passe actuel incorrect !");</script>';
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Changer Mot de Passe - Système de Gestion des Missions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include_once('includes/sidebar.php'); ?>
    
    <div class="main-content">
        <?php include_once('includes/header.php'); ?>
        
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-key"></i> Changer mon Mot de Passe</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Accueil</a></li>
                        <li class="breadcrumb-item active">Changer Mot de Passe</li>
                    </ol>
                </nav>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header bg-warning text-white">
                            <h5 class="mb-0"><i class="fas fa-lock"></i> Modification du Mot de Passe</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" onsubmit="return validatePassword();">
                                <div class="form-group">
                                    <label for="currentpassword">Mot de Passe Actuel <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                        </div>
                                        <input type="password" class="form-control" id="currentpassword" name="currentpassword" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="newpassword">Nouveau Mot de Passe <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="fas fa-key"></i>
                                            </span>
                                        </div>
                                        <input type="password" class="form-control" id="newpassword" name="newpassword" minlength="6" required>
                                    </div>
                                    <small class="form-text text-muted">Le mot de passe doit contenir au moins 6 caractères.</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirmpassword">Confirmer Nouveau Mot de Passe <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="fas fa-check"></i>
                                            </span>
                                        </div>
                                        <input type="password" class="form-control" id="confirmpassword" name="confirmpassword" minlength="6" required>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Conseils de sécurité :</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Utilisez un mot de passe d'au moins 8 caractères</li>
                                        <li>Combinez lettres, chiffres et caractères spéciaux</li>
                                        <li>Évitez les mots de passe évidents</li>
                                    </ul>
                                </div>
                                
                                <div class="form-group text-center">
                                    <button type="submit" name="submit" class="btn btn-warning btn-lg">
                                        <i class="fas fa-save"></i> Changer le Mot de Passe
                                    </button>
                                    <a href="profile.php" class="btn btn-secondary btn-lg ml-2">
                                        <i class="fas fa-times"></i> Annuler
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
    
    <script>
    function validatePassword() {
        var newPassword = document.getElementById('newpassword').value;
        var confirmPassword = document.getElementById('confirmpassword').value;
        
        if (newPassword !== confirmPassword) {
            alert('Les nouveaux mots de passe ne correspondent pas !');
            return false;
        }
        
        if (newPassword.length < 6) {
            alert('Le nouveau mot de passe doit contenir au moins 6 caractères !');
            return false;
        }
        
        return confirm('Êtes-vous sûr de vouloir changer votre mot de passe ?');
    }
    </script>
</body>
</html>

<?php } ?>
