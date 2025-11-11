<div class="sidebar bg-success text-white">
    <div class="sidebar-header p-3">
        <h5><i class="fas fa-user-check"></i> Chef de Département</h5>
    </div>
    
    <nav class="nav flex-column">
        <a class="nav-link text-white" href="dashboard.php">
            <i class="fas fa-tachometer-alt"></i> Tableau de Bord
        </a>
        
        <div class="nav-section">
            <h6 class="nav-header text-light px-3 py-2">VALIDATION DES MISSIONS</h6>
            <a class="nav-link text-white" href="pending-missions.php">
                <i class="fas fa-clock"></i> Demandes en Attente
                <?php
                $cid = $_SESSION['GMScid'];
                $sql = "SELECT COUNT(*) as total FROM tblmissions WHERE Status='en_attente'";
                $query = $dbh->prepare($sql);
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
            <a class="nav-link text-white" href="rejected-missions.php">
                <i class="fas fa-times-circle"></i> Missions Rejetées
            </a>
        </div>
        
        <div class="nav-section">
            <h6 class="nav-header text-light px-3 py-2">SUIVI</h6>
            <a class="nav-link text-white" href="all-missions.php">
                <i class="fas fa-list"></i> Toutes les Missions
            </a>
            <a class="nav-link text-white" href="statistics.php">
                <i class="fas fa-chart-line"></i> Statistiques
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
    padding-top: 60px;
    background: linear-gradient(180deg, #28a745 0%, #20c997 100%) !important;
}

.main-content {
    margin-left: 250px;
    padding-top: 60px;
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
    background-color: #20c997;
}
</style>
