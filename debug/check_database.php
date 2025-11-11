<?php
try {
    $dbh = new PDO("mysql:host=localhost;dbname=gestion_missions_db;charset=utf8", "root", "", array(
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ));
    
    echo "<h2>🔍 Diagnostic de la Base de Données</h2>";
    
    // 1. Vérifier si la table existe
    $sql = "SHOW TABLES LIKE 'tblmissions'";
    $query = $dbh->prepare($sql);
    $query->execute();
    
    if($query->rowCount() > 0) {
        echo "✅ Table 'tblmissions' existe<br>";
        
        // 2. Vérifier la structure
        $sql = "DESCRIBE tblmissions";
        $query = $dbh->prepare($sql);
        $query->execute();
        $columns = $query->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>📋 Structure de la table :</h3>";
        echo "<table border='1'>";
        echo "<tr><th>Colonne</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th></tr>";
        foreach($columns as $col) {
            echo "<tr>";
            echo "<td>".$col['Field']."</td>";
            echo "<td>".$col['Type']."</td>";
            echo "<td>".$col['Null']."</td>";
            echo "<td>".$col['Key']."</td>";
            echo "<td>".$col['Default']."</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // 3. Compter les enregistrements
        $sql = "SELECT COUNT(*) as total FROM tblmissions";
        $query = $dbh->prepare($sql);
        $query->execute();
        $total = $query->fetch(PDO::FETCH_OBJ)->total;
        echo "<br>📊 Total missions dans la table: <strong>$total</strong><br>";
        
        // 4. Afficher les dernières missions
        $sql = "SELECT * FROM tblmissions ORDER BY DateCreation DESC LIMIT 5";
        $query = $dbh->prepare($sql);
        $query->execute();
        $missions = $query->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>📝 Dernières missions :</h3>";
        if(count($missions) > 0) {
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Reference</th><th>NomPrenom</th><th>Status</th><th>DateCreation</th></tr>";
            foreach($missions as $mission) {
                echo "<tr>";
                echo "<td>".$mission['ID']."</td>";
                echo "<td>".$mission['ReferenceNumber']."</td>";
                echo "<td>".$mission['NomPrenom']."</td>";
                echo "<td>".$mission['Status']."</td>";
                echo "<td>".$mission['DateCreation']."</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "❌ Aucune mission trouvée";
        }
        
    } else {
        echo "❌ Table 'tblmissions' n'existe PAS !<br>";
        echo "<strong>Création de la table...</strong><br>";
        
        // Créer la table
        $sql = "CREATE TABLE `tblmissions` (
            `ID` int(11) NOT NULL AUTO_INCREMENT,
            `UserID` int(11) NOT NULL,
            `ReferenceNumber` varchar(50) NOT NULL,
            `NomPrenom` varchar(255) NOT NULL,
            `Fonction` varchar(255) NOT NULL,
            `VilleDepart` varchar(255) NOT NULL,
            `DateDepart` date NOT NULL,
            `Destinations` text NOT NULL,
            `ItineraireType` varchar(100) NOT NULL,
            `MotifDeplacement` varchar(255) NOT NULL,
            `MoyenTransport` varchar(255) NOT NULL,
            `DateRetour` date NOT NULL,
            `Observations` text DEFAULT NULL,
            `Status` enum('en_attente','validee','rejetee','en_cours') NOT NULL DEFAULT 'en_attente',
            `Remarque` text DEFAULT NULL,
            `ValidatedBy` int(11) DEFAULT NULL,
            `DateCreation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `DateValidation` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`ID`),
            KEY `UserID` (`UserID`),
            KEY `ValidatedBy` (`ValidatedBy`),
            KEY `Status` (`Status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if($dbh->exec($sql)) {
            echo "✅ Table 'tblmissions' créée avec succès !<br>";
        } else {
            echo "❌ Erreur lors de la création de la table<br>";
        }
    }
    
    // 5. Vérifier les utilisateurs
    echo "<h3>👥 Utilisateurs disponibles :</h3>";
    $sql = "SELECT ID, Nom, Prenom, Fonction, Role FROM tblusers ORDER BY Nom";
    $query = $dbh->prepare($sql);
    $query->execute();
    $users = $query->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Fonction</th><th>Rôle</th></tr>";
    foreach($users as $user) {
        echo "<tr>";
        echo "<td>".$user['ID']."</td>";
        echo "<td>".$user['Nom']."</td>";
        echo "<td>".$user['Prenom']."</td>";
        echo "<td>".$user['Fonction']."</td>";
        echo "<td>".$user['Role']."</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch(Exception $e) {
    echo "❌ Erreur de connexion: " . $e->getMessage();
}
?>
