<div class="sidebar bg-info text-white">
    <div class="sidebar-header p-3">
        <h5><i class="fas fa-user"></i> Mon Espace</h5>
    </div>
    
    <nav class="nav flex-column">
        <a class="nav-link text-white" href="dashboard.php">
            <i class="fas fa-tachometer-alt"></i> Tableau de Bord
        </a>
        
        <div class="nav-section">
            <h6 class="nav-header text-light px-3 py-2">MES MISSIONS</h6>
            <a class="nav-link text-white" href="create-mission.php">
                <i class="fas fa-plus-circle"></i> Nouvelle Demande
            </a>
            <a class="nav-link text-white" href="my-missions.php">
                <i class="fas fa-list"></i> Mes Demandes
                <?php
                $uid = $_SESSION['GMSuid'];
                $sql = "SELECT COUNT(*) as total FROM tblmissions WHERE UserID=:uid AND Status='en_attente'";
                $query = $dbh->prepare($sql);
                $query->bindParam(':uid', $uid, PDO::PARAM_STR);
                $query->execute();
                $pending = $query->fetch(PDO::FETCH_OBJ)->total;
                if($pending > 0) {
                    echo '<span class="badge badge-warning ml-2">'.$pending.'</span>';
                }
                ?>
            </a>
            <a class="nav-link text-white" href="validated-missions.php">
                <i class="fas fa-check-circle"></i> Missions Validées
            </a>
        </div>
        
        <div class="nav-section">
            <h6 class="nav-header text-light px-3 py-2">MON COMPTE</h6>
            <a class="nav-link text-white" href="profile.php">
                <i class="fas fa-user-edit"></i> Mon Profil
            </a>
            <a class="nav-link text-white" href="change-password.php">
                <i class="fas fa-key"></i> Mot de Passe
            </a>
        </div>
    </nav>
</div>

<style>
.sidebar {
    min-height: 100vh;
    width: 250px;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 100;
    padding-top: 5px;
    background: linear-gradient(180deg, #17a2b8 0%, #138496 100%) !important;
}

.dropdown-menu.show {
  margin-left: -5rem;
}

.main-content {
    margin-left: 250px;
}

.nav-section {
    margin-bottom: 20px;
}

.nav-header {
    font-size: 0.8rem;
    font-weight: bold;
    letter-spacing: 1px;
}

.nav-link {
    padding: 10px 20px;
    border-radius: 0;
    transition: background-color 0.3s;
}

.nav-link:hover {
    background-color: rgba(255,255,255,0.1);
}

.nav-link.active {
    background-color: #138496;
}
</style>
