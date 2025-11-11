<nav class="navbar navbar-expand-lg navbar-dark bg-info">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">
            <i class="fas fa-map-marked-alt"></i> SNTP - Mes Missions
        </a>
        
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown">
                        <i class="fas fa-user-circle"></i>
                        <?php 
                        $uid = $_SESSION['GMSuid'];
                        $sql = "SELECT Nom,Prenom FROM tblusers WHERE ID=:uid";
                        $query = $dbh->prepare($sql);
                        $query->bindParam(':uid',$uid,PDO::PARAM_STR);
                        $query->execute();
                        $result = $query->fetch(PDO::FETCH_OBJ);
                        echo htmlentities($result->Prenom . ' ' . $result->Nom);
                        ?>
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="profile.php">
                            <i class="fas fa-user"></i> Mon Profil
                        </a>
                        <a class="dropdown-item" href="change-password.php">
                            <i class="fas fa-key"></i> Changer mot de passe
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>
