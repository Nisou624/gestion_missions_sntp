<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['GMSaid']) == 0) {
    header('location:logout.php');
} else {
    if(isset($_POST['submit'])) {
        $aid = $_SESSION['GMSaid'];
        $currentpassword = $_POST['currentpassword'];
        $newpassword = $_POST['newpassword'];
        $confirmpassword = $_POST['confirmpassword'];
        
        // Vérifier que les nouveaux mots de passe correspondent
        if($newpassword !== $confirmpassword) {
            echo '<script>alert("Les nouveaux mots de passe ne correspondent pas !");</script>';
        } else {
            // Vérifier le mot de passe actuel
            $sql = "SELECT Password FROM tblusers WHERE ID=:aid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':aid', $aid, PDO::PARAM_STR);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_OBJ);
            
            if(password_verify($currentpassword, $result->Password)) {
                $new_hash = password_hash($newpassword, PASSWORD_DEFAULT);
                $con = "UPDATE tblusers SET Password=:newpassword WHERE ID=:aid";
                $chngpwd1 = $dbh->prepare($con);
                $chngpwd1->bindParam(':aid', $aid, PDO::PARAM_STR);
                $chngpwd1->bindParam(':newpassword', $new_hash, PDO::PARAM_STR);
                $chngpwd1->execute();
                
                echo '<script>alert("Mot de passe administrateur changé avec succès !");</script>';
                echo "<script>window.location.href ='profile.php'</script>";
            } else {
                echo '<script>alert("Mot de passe actuel incorrect !");</script>';
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Changer Mot de Passe Admin - Système de Gestion des Missions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include_once('includes/sidebar.php'); ?>
    
    <div class="main-content">
        <?php include_once('includes/header.php'); ?>
        
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-shield-alt"></i> Changer mon Mot de Passe Administrateur</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Accueil</a></li>
                        <li class="breadcrumb-item"><a href="profile.php">Mon Profil</a></li>
                        <li class="breadcrumb-item active">Changer Mot de Passe</li>
                    </ol>
                </nav>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- Alerte de sécurité -->
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Attention !</strong> Vous êtes sur le point de modifier le mot de passe d'un compte administrateur. 
                        Assurez-vous de choisir un mot de passe très sécurisé et de le conserver dans un endroit sûr.
                    </div>
                    
                    <div class="card border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-lock"></i> Modification du Mot de Passe Administrateur
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" id="passwordForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="currentpassword">
                                                Mot de Passe Actuel <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-lock"></i>
                                                    </span>
                                                </div>
                                                <input type="password" class="form-control" id="currentpassword" 
                                                       name="currentpassword" required autocomplete="current-password">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-outline-secondary" 
                                                            onclick="togglePassword('currentpassword')">
                                                        <i class="fas fa-eye" id="currentpassword-icon"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="newpassword">
                                                Nouveau Mot de Passe <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-key"></i>
                                                    </span>
                                                </div>
                                                <input type="password" class="form-control" id="newpassword" 
                                                       name="newpassword" minlength="8" required autocomplete="new-password">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-outline-secondary" 
                                                            onclick="togglePassword('newpassword')">
                                                        <i class="fas fa-eye" id="newpassword-icon"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="password-strength mt-2">
                                                <div class="progress" style="height: 5px;">
                                                    <div id="strength-bar" class="progress-bar" style="width: 0%"></div>
                                                </div>
                                                <small id="strength-text" class="form-text text-muted"></small>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="confirmpassword">
                                                Confirmer Nouveau Mot de Passe <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-check-double"></i>
                                                    </span>
                                                </div>
                                                <input type="password" class="form-control" id="confirmpassword" 
                                                       name="confirmpassword" minlength="8" required autocomplete="new-password">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-outline-secondary" 
                                                            onclick="togglePassword('confirmpassword')">
                                                        <i class="fas fa-eye" id="confirmpassword-icon"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div id="password-match" class="mt-1"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="card bg-light">
                                            <div class="card-header">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-shield-check"></i> Exigences de Sécurité
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <p class="text-muted mb-3">
                                                    <strong>Votre mot de passe doit contenir :</strong>
                                                </p>
                                                <ul class="list-unstyled">
                                                    <li id="length-req" class="text-muted">
                                                        <i class="fas fa-times text-danger"></i> Au moins 8 caractères
                                                    </li>
                                                    <li id="uppercase-req" class="text-muted">
                                                        <i class="fas fa-times text-danger"></i> Une lettre majuscule
                                                    </li>
                                                    <li id="lowercase-req" class="text-muted">
                                                        <i class="fas fa-times text-danger"></i> Une lettre minuscule
                                                    </li>
                                                    <li id="number-req" class="text-muted">
                                                        <i class="fas fa-times text-danger"></i> Un chiffre
                                                    </li>
                                                    <li id="special-req" class="text-muted">
                                                        <i class="fas fa-times text-danger"></i> Un caractère spécial (!@#$%^&*)
                                                    </li>
                                                </ul>
                                                
                                                <hr>
                                                
                                                <div class="alert alert-warning alert-sm">
                                                    <small>
                                                        <i class="fas fa-lightbulb"></i>
                                                        <strong>Conseil :</strong> Utilisez une phrase de passe 
                                                        avec des mots, chiffres et symboles.
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>Important :</strong> Après avoir changé votre mot de passe, 
                                            vous serez automatiquement déconnecté et devrez vous reconnecter avec 
                                            le nouveau mot de passe.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group text-center">
                                    <button type="submit" name="submit" class="btn btn-danger btn-lg" id="submitBtn" disabled>
                                        <i class="fas fa-shield-alt"></i> Changer le Mot de Passe Administrateur
                                    </button>
                                    <a href="profile.php" class="btn btn-secondary btn-lg ml-3">
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
    // Fonctions de basculement de visibilité du mot de passe
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + '-icon');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    
    // Validation en temps réel du mot de passe
    document.getElementById('newpassword').addEventListener('input', function() {
        const password = this.value;
        checkPasswordStrength(password);
        checkPasswordMatch();
    });
    
    document.getElementById('confirmpassword').addEventListener('input', function() {
        checkPasswordMatch();
    });
    
    function checkPasswordStrength(password) {
        let strength = 0;
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /\d/.test(password),
            special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\?]/.test(password)
        };
        
        // Mettre à jour les icônes des exigences
        Object.keys(requirements).forEach(req => {
            const element = document.getElementById(req + '-req');
            const icon = element.querySelector('i');
            
            if (requirements[req]) {
                icon.classList.remove('fa-times', 'text-danger');
                icon.classList.add('fa-check', 'text-success');
                element.classList.remove('text-muted');
                element.classList.add('text-success');
                strength++;
            } else {
                icon.classList.remove('fa-check', 'text-success');
                icon.classList.add('fa-times', 'text-danger');
                element.classList.remove('text-success');
                element.classList.add('text-muted');
            }
        });
        
        // Barre de force du mot de passe
        const strengthBar = document.getElementById('strength-bar');
        const strengthText = document.getElementById('strength-text');
        
        const percentage = (strength / 5) * 100;
        strengthBar.style.width = percentage + '%';
        
        if (strength <= 2) {
            strengthBar.className = 'progress-bar bg-danger';
            strengthText.textContent = 'Mot de passe faible';
            strengthText.className = 'form-text text-danger';
        } else if (strength <= 3) {
            strengthBar.className = 'progress-bar bg-warning';
            strengthText.textContent = 'Mot de passe moyen';
            strengthText.className = 'form-text text-warning';
        } else if (strength <= 4) {
            strengthBar.className = 'progress-bar bg-info';
            strengthText.textContent = 'Mot de passe fort';
            strengthText.className = 'form-text text-info';
        } else {
            strengthBar.className = 'progress-bar bg-success';
            strengthText.textContent = 'Mot de passe très fort';
            strengthText.className = 'form-text text-success';
        }
        
        return strength >= 4;
    }
    
    function checkPasswordMatch() {
        const newPassword = document.getElementById('newpassword').value;
        const confirmPassword = document.getElementById('confirmpassword').value;
        const matchDiv = document.getElementById('password-match');
        
        if (confirmPassword.length === 0) {
            matchDiv.innerHTML = '';
            return false;
        }
        
        if (newPassword === confirmPassword) {
            matchDiv.innerHTML = '<small class="text-success"><i class="fas fa-check"></i> Les mots de passe correspondent</small>';
            return true;
        } else {
            matchDiv.innerHTML = '<small class="text-danger"><i class="fas fa-times"></i> Les mots de passe ne correspondent pas</small>';
            return false;
        }
    }
    
    // Validation du formulaire
    document.getElementById('passwordForm').addEventListener('input', function() {
        const currentPassword = document.getElementById('currentpassword').value;
        const newPassword = document.getElementById('newpassword').value;
        const confirmPassword = document.getElementById('confirmpassword').value;
        const submitBtn = document.getElementById('submitBtn');
        
        const isStrong = checkPasswordStrength(newPassword);
        const isMatching = checkPasswordMatch();
        
        if (currentPassword.length > 0 && isStrong && isMatching && newPassword.length >= 8) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('btn-secondary');
            submitBtn.classList.add('btn-danger');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.remove('btn-danger');
            submitBtn.classList.add('btn-secondary');
        }
    });
    
    // Confirmation avant soumission
    document.getElementById('passwordForm').addEventListener('submit', function(e) {
        if (!confirm('Êtes-vous absolument sûr de vouloir changer votre mot de passe administrateur ? Vous serez déconnecté après cette action.')) {
            e.preventDefault();
            return false;
        }
    });
    </script>
    
    <?php include_once('includes/footer.php'); ?>
</body>
</html>

<?php } ?>
