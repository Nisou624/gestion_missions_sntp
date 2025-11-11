<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['GMSaid']) == 0) {
    header('location:logout.php');
} else {
    if(isset($_POST['submit'])) {
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $email = $_POST['email'];
        $fonction = $_POST['fonction'];
        $departement = $_POST['departement'];
        $mobile = $_POST['mobile'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];
        
        // Vérifier si l'email existe déjà
        $ret = "SELECT Email FROM tblusers WHERE Email=:email";
        $query = $dbh->prepare($ret);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->execute();
        
        if($query->rowCount() == 0) {
            $sql = "INSERT INTO tblusers(Nom,Prenom,Email,Fonction,Departement,MobileNumber,Password,Role) 
                    VALUES(:nom,:prenom,:email,:fonction,:departement,:mobile,:password,:role)";
            $query = $dbh->prepare($sql);
            $query->bindParam(':nom', $nom, PDO::PARAM_STR);
            $query->bindParam(':prenom', $prenom, PDO::PARAM_STR);
            $query->bindParam(':email', $email, PDO::PARAM_STR);
            $query->bindParam(':fonction', $fonction, PDO::PARAM_STR);
            $query->bindParam(':departement', $departement, PDO::PARAM_STR);
            $query->bindParam(':mobile', $mobile, PDO::PARAM_STR);
            $query->bindParam(':password', $password, PDO::PARAM_STR);
            $query->bindParam(':role', $role, PDO::PARAM_STR);
            $query->execute();
            
            $LastInsertId = $dbh->lastInsertId();
            if ($LastInsertId > 0) {
                echo '<script>alert("Utilisateur ajouté avec succès !");</script>';
                echo "<script>window.location.href ='manage-users.php'</script>";
            } else {
                echo '<script>alert("Erreur lors de l\'ajout de l\'utilisateur !");</script>';
            }
        } else {
            echo "<script>alert('Cet email est déjà utilisé !');</script>";
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter Utilisateur - Système de Gestion des Missions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include_once('includes/sidebar.php'); ?>
    
    <div class="main-content">
        <?php include_once('includes/header.php'); ?>
        
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-user-plus"></i> Ajouter un Utilisateur</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Accueil</a></li>
                        <li class="breadcrumb-item"><a href="manage-users.php">Utilisateurs</a></li>
                        <li class="breadcrumb-item active">Ajouter</li>
                    </ol>
                </nav>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Informations du Nouvel Utilisateur</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nom">Nom <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="nom" name="nom" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="prenom">Prénom <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="prenom" name="prenom" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Adresse Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="fonction">Fonction <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="fonction" name="fonction" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="departement">Département <span class="text-danger">*</span></label>
                                            <select class="form-control" id="departement" name="departement" required>
                                                <option value="">Sélectionner un département</option>
                                                <option value="Direction Générale">Direction Générale</option>
                                                <option value="Audit et Contrôle">Audit et Contrôle</option>
                                                <option value="Travaux Publics">Travaux Publics</option>
                                                <option value="Ressources Humaines">Ressources Humaines</option>
                                                <option value="Finances et Comptabilité">Finances et Comptabilité</option>
                                                <option value="Informatique">Informatique</option>
                                                <option value="Logistique">Logistique</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="mobile">Numéro de Téléphone</label>
                                            <input type="tel" class="form-control" id="mobile" name="mobile">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="role">Rôle <span class="text-danger">*</span></label>
                                            <select class="form-control" id="role" name="role" required>
                                                <option value="">Sélectionner un rôle</option>
                                                <option value="user">Utilisateur</option>
                                                <option value="chef_departement">Chef de Département</option>
                                                <option value="admin">Administrateur</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="password">Mot de Passe <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="password" name="password" minlength="6" required>
                                    <small class="form-text text-muted">Le mot de passe doit contenir au moins 6 caractères.</small>
                                </div>
                                
                                <div class="form-group text-center">
                                    <button type="submit" name="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save"></i> Ajouter l'Utilisateur
                                    </button>
                                    <a href="manage-users.php" class="btn btn-secondary btn-lg ml-2">
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
    
    <?php include_once('includes/footer.php'); ?>
</body>
</html>

<?php } ?>
