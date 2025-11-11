<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if(isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $sql = "SELECT ID,Password,Role FROM tblusers WHERE Email=:username AND Role='chef_departement'";
    $query = $dbh->prepare($sql);
    $query->bindParam(':username', $username, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);
    
    if($result && password_verify($password, $result->Password)) {
        $_SESSION['GMScid'] = $result->ID;
        $_SESSION['login'] = $username;
        echo "<script>alert('Connexion réussie !'); window.location.href='dashboard.php';</script>";
    } else {
        echo "<script>alert('Identifiants invalides !');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Chef de Département - Système de Gestion des Missions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            padding: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-card">
                    <div class="login-header">
                        <i class="fas fa-user-check fa-3x text-success mb-3"></i>
                        <h3>Connexion Chef de Département</h3>
                        <p class="text-muted">Validation des Ordres de Mission - SNTP</p>
                    </div>
                    
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
                            Email: i.bourahla@sntp.dz<br>
                            Mot de passe: chef123
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
