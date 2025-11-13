<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
require_once('../includes/security.php');

$securityManager = new SecurityManager($dbh);
$errorMessage = '';
$infoMessage = '';

if(isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Vérifier si le compte est verrouillé
    if ($securityManager->isAccountLocked($username)) {
        $errorMessage = "Votre compte est temporairement verrouillé en raison de tentatives de connexion échouées. Veuillez réessayer dans 15 minutes.";
        ActivityLogger::log($dbh, null, $username, 'login_attempt_locked', 'user', null, 
            'Tentative de connexion sur compte verrouillé', 'warning');
    } else {
        $sql = "SELECT ID, Password, Role FROM tblusers WHERE Email=:username AND Role='admin'";
        $query = $dbh->prepare($sql);
        $query->bindParam(':username', $username, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_OBJ);
        
        if($result && password_verify($password, $result->Password)) {
            // Connexion réussie
            $securityManager->recordLoginAttempt($username, $securityManager->getClientIP(), true);
            
            $_SESSION['GMSaid'] = $result->ID;
            $_SESSION['login'] = $username;
            $_SESSION['role'] = 'admin';
            
            // Créer une session sécurisée
            $securityManager->createSecureSession($result->ID);
            
            // Logger l'activité
            ActivityLogger::log($dbh, $result->ID, $username, 'login_success', 'user', $result->ID, 
                'Connexion administrateur réussie', 'success');
            
            header('Location: dashboard.php');
            exit();
        } else {
            // Connexion échouée
            $securityManager->recordLoginAttempt($username, $securityManager->getClientIP(), false);
            $remainingAttempts = $securityManager->getRemainingAttempts($username);
            
            ActivityLogger::log($dbh, null, $username, 'login_failed', 'user', null, 
                'Tentative de connexion échouée', 'failure');
            
            if ($remainingAttempts > 0) {
                $errorMessage = "Identifiants invalides. Il vous reste $remainingAttempts tentative(s).";
            } else {
                $errorMessage = "Identifiants invalides. Votre compte a été verrouillé pour 15 minutes.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin - Système de Gestion des Missions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .btn-login {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            padding: 12px;
            font-weight: bold;
        }
        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-card">
                    <div class="login-header">
                        <i class="fas fa-user-shield fa-3x text-primary mb-3"></i>
                        <h3>Connexion Administrateur</h3>
                        <p class="text-muted">Système de Gestion des Missions - SNTP</p>
                    </div>
                    
                    <?php if($errorMessage): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $errorMessage; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($infoMessage): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <?php echo $infoMessage; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="username">Email</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                </div>
                                <input type="email" class="form-control" id="username" name="username" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Mot de passe</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                </div>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        
                        <button type="submit" name="login" class="btn btn-login btn-block text-white">
                            <i class="fas fa-sign-in-alt"></i> Se Connecter
                        </button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <a href="../index.php" class="text-muted">
                            <i class="fas fa-arrow-left"></i> Retour à l'accueil
                        </a>
                    </div>
                    
                    <div class="mt-4 p-3 bg-light rounded">
                        <small class="text-muted">
                            <strong>Compte de démonstration :</strong><br>
                            Email: admin@sntp.dz<br>
                            Mot de passe: admin123
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

