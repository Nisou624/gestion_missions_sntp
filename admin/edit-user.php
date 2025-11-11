<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['GMSaid']) == 0) {
    header('location:logout.php');
} else {
    $eid = intval($_GET['editid']);
    
    if(isset($_POST['submit'])) {
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $email = $_POST['email'];
        $fonction = $_POST['fonction'];
        $departement = $_POST['departement'];
        $mobile = $_POST['mobile'];
        $role = $_POST['role'];
        
        // Vérifier si l'email existe déjà pour un autre utilisateur
        $ret = "SELECT Email FROM tblusers WHERE Email=:email AND ID!=:eid";
        $query = $dbh->prepare($ret);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':eid', $eid, PDO::PARAM_STR);
        $query->execute();
        
        if($query->rowCount() == 0) {
            $sql = "UPDATE tblusers SET Nom=:nom, Prenom=:prenom, Email=:email, Fonction=:fonction, 
                    Departement=:departement, MobileNumber=:mobile, Role=:role WHERE ID=:eid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':nom', $nom, PDO::PARAM_STR);
            $query->bindParam(':prenom', $prenom, PDO::PARAM_STR);
            $query->bindParam(':email', $email, PDO::PARAM_STR);
            $query->bindParam(':fonction', $fonction, PDO::PARAM_STR);
            $query->bindParam(':departement', $departement, PDO::PARAM_STR);
            $query->bindParam(':mobile', $mobile, PDO::PARAM_STR);
            $query->bindParam(':role', $role, PDO::PARAM_STR);
            $query->bindParam(':eid', $eid, PDO::PARAM_STR);
            $query->execute();
            
            echo '<script>alert("Utilisateur modifié avec succès !");</script>';
            echo "<script>window.location.href ='manage-users.php'</script>";
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
    <title>Modifier Utilisateur - Système de Gestion des Missions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include_once('includes/sidebar.php'); ?>
    
    <div class="main-content">
        <?php include_once('includes/header.php'); ?>
        
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-user-edit"></i> Modifier l'Utilisateur</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Accueil</a></li>
                        <li class="breadcrumb-item"><a href="manage-users.php">Utilisateurs</a></li>
                        <li class="breadcrumb-item active">Modifier</li>
                    </ol>
                </nav>
            </div>
            
            <?php
            $sql = "SELECT * FROM tblusers WHERE ID=:eid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':eid', $eid, PDO::PARAM_STR);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_OBJ);
            
            if($query->rowCount() > 0) {
            ?>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Informations de l'Utilisateur</h5>
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
                                            <label for="departement">Département <span class="text-danger">*</span></label>
                                            <select class="form-control" id="departement" name="departement" required>
                                                <option value="">Sélectionner un département</option>
                                                <option value="Direction Générale" <?php if($result->Departement == 'Direction Générale') echo 'selected'; ?>>Direction Générale</option>
                                                <option value="Audit et Contrôle" <?php if($result->Departement == 'Audit et Contrôle') echo 'selected'; ?>>Audit et Contrôle</option>
                                                <option value="Travaux Publics" <?php if($result->Departement == 'Travaux Publics') echo 'selected'; ?>>Travaux Publics</option>
                                                <option value="Ressources Humaines" <?php if($result->Departement == 'Ressources Humaines') echo 'selected'; ?>>Ressources Humaines</option>
                                                <option value="Finances et Comptabilité" <?php if($result->Departement == 'Finances et Comptabilité') echo 'selected'; ?>>Finances et Comptabilité</option>
                                                <option value="Informatique" <?php if($result->Departement == 'Informatique') echo 'selected'; ?>>Informatique</option>
                                                <option value="Logistique" <?php if($result->Departement == 'Logistique') echo 'selected'; ?>>Logistique</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="mobile">Numéro de Téléphone</label>
                                            <input type="tel" class="form-control" id="mobile" name="mobile" 
                                                   value="<?php echo htmlentities($result->MobileNumber); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="role">Rôle <span class="text-danger">*</span></label>
                                            <select class="form-control" id="role" name="role" required>
                                                <option value="">Sélectionner un rôle</option>
                                                <option value="user" <?php if($result->Role == 'user') echo 'selected'; ?>>Utilisateur</option>
                                                <option value="chef_departement" <?php if($result->Role == 'chef_departement') echo 'selected'; ?>>Chef de Département</option>
                                                <option value="admin" <?php if($result->Role == 'admin') echo 'selected'; ?>>Administrateur</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Date d'Inscription</label>
                                    <input type="text" class="form-control" 
                                           value="<?php echo date('d/m/Y H:i', strtotime($result->RegDate)); ?>" readonly>
                                </div>
                                
                                <div class="form-group text-center">
                                    <button type="submit" name="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save"></i> Sauvegarder les Modifications
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
            
            <?php } else { ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                Utilisateur non trouvé.
            </div>
            <?php } ?>
        </div>
    </div>
    
    <?php include_once('includes/footer.php'); ?>
</body>
</html>

<?php } ?>
