<?php
session_start();
error_reporting(0);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Système de Gestion des Ordres de Mission - SNTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .hero-section {
            padding: 80px 0;
            color: white;
            text-align: center;
        }
        .card-access {
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s;
            margin: 20px 0;
        }
        .card-access:hover {
            transform: translateY(-5px);
        }
        .btn-access {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        .btn-access:hover {
            background: linear-gradient(45deg, #764ba2, #667eea);
            color: white;
            text-decoration: none;
            transform: scale(1.05);
        }
        .logo-section {
            background: white;
            padding: 20px;
            margin-bottom: 40px;
            border-radius: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="hero-section">
            <div class="logo-section">
                <h1 class="text-dark">
                    <i class="fas fa-road text-primary"></i>
                    Société Nationale de Travaux Publics
                </h1>
                <p class="text-muted mb-0">Système de Gestion des Ordres de Mission</p>
            </div>
            
            <h2 class="mb-5">Accès au Système</h2>
            
            <div class="row justify-content-center" style="color: black;">
                <div class="col-md-4">
                    <div class="card card-access">
                        <div class="card-body">
                            <i class="fas fa-user-tie fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Administrateur</h5>
                            <p class="card-text">Gestion complète du système, utilisateurs et rapports</p>
                            <a href="admin/login.php" class="btn btn-access">
                                <i class="fas fa-sign-in-alt"></i> Connexion Admin
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card card-access">
                        <div class="card-body">
                            <i class="fas fa-user-check fa-3x text-success mb-3"></i>
                            <h5 class="card-title">Chef de Département</h5>
                            <p class="card-text">Validation et suivi des demandes de mission</p>
                            <a href="chef/login.php" class="btn btn-access">
                                <i class="fas fa-sign-in-alt"></i> Connexion Chef
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card card-access">
                        <div class="card-body">
                            <i class="fas fa-user fa-3x text-info mb-3"></i>
                            <h5 class="card-title">Utilisateur</h5>
                            <p class="card-text">Création et suivi de vos demandes de mission</p>
                            <a href="user/login.php" class="btn btn-access">
                                <i class="fas fa-sign-in-alt"></i> Connexion Utilisateur
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-5">
                <p class="text-light">
                    <i class="fas fa-info-circle"></i>
                    Pour une première connexion, utilisez les identifiants de démonstration
                </p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
