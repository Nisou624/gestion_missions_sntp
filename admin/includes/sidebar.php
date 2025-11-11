<div class="sidebar bg-dark text-white">
    <div class="sidebar-header p-3">
        <h5><i class="fas fa-cogs"></i> Administration</h5>
    </div>
    
    <nav class="nav flex-column">
        <a class="nav-link text-white" href="dashboard.php">
            <i class="fas fa-tachometer-alt"></i> Tableau de Bord
        </a>
        
        <div class="nav-section">
            <h6 class="nav-header text-muted px-3 py-2">GESTION DES MISSIONS</h6>
            <a class="nav-link text-white" href="all-missions.php">
                <i class="fas fa-list"></i> Toutes les Missions
            </a>
            <a class="nav-link text-white" href="new-missions.php">
                <i class="fas fa-clock"></i> Nouvelles Demandes
            </a>
            <a class="nav-link text-white" href="validated-missions.php">
                <i class="fas fa-check-circle"></i> Missions Validées
            </a>
            <a class="nav-link text-white" href="rejected-missions.php">
                <i class="fas fa-times-circle"></i> Missions Rejetées
            </a>
        </div>
        
        <div class="nav-section">
            <h6 class="nav-header text-muted px-3 py-2">GESTION DES UTILISATEURS</h6>
            <a class="nav-link text-white" href="manage-users.php">
                <i class="fas fa-users"></i> Gérer Utilisateurs
            </a>
            <a class="nav-link text-white" href="add-user.php">
                <i class="fas fa-user-plus"></i> Ajouter Utilisateur
            </a>
        </div>
        
        <div class="nav-section">
            <h6 class="nav-header text-muted px-3 py-2">RAPPORTS</h6>
            <a class="nav-link text-white" href="reports.php">
                <i class="fas fa-chart-bar"></i> Statistiques
            </a>
            <a class="nav-link text-white" href="between-dates-report.php">
                <i class="fas fa-calendar-alt"></i> Rapport par Période
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
    background-color: #007bff;
}
</style>
