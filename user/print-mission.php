<?php
session_start();
error_reporting(0);
include('../includes/config.php');

if (strlen($_SESSION['GMSuid']) == 0) {
    header('location:logout.php');
} else {
    $uid = $_SESSION['GMSuid'];
    $mid = intval($_GET['mid']);
    
    // Vérifier que la mission appartient à l'utilisateur et est validée
    $sql = "SELECT m.*, u.Nom, u.Prenom, u.Fonction as UserFonction, u.Departement,
                   v.Nom as ValidatorNom, v.Prenom as ValidatorPrenom, v.Fonction as ValidatorFonction
            FROM tblmissions m 
            JOIN tblusers u ON m.UserID = u.ID 
            LEFT JOIN tblusers v ON m.ValidatedBy = v.ID
            WHERE m.ID = :mid AND m.UserID = :uid AND m.Status = 'validee'";
    $query = $dbh->prepare($sql);
    $query->bindParam(':mid', $mid, PDO::PARAM_STR);
    $query->bindParam(':uid', $uid, PDO::PARAM_STR);
    $query->execute();
    $mission = $query->fetch(PDO::FETCH_OBJ);
    
    if($query->rowCount() == 0) {
        echo "<script>alert('Mission non trouvée ou non autorisée !'); window.location.href='my-missions.php';</script>";
        exit();
    }

    // Définir le type de contenu comme PDF si on veut télécharger
    if(isset($_GET['download'])) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="ordre_mission_'.$mission->ReferenceNumber.'.pdf"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordre de Mission - <?php echo htmlspecialchars($mission->ReferenceNumber); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: white;
        }
        
        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 20mm;
            background: white;
            position: relative;
        }
        
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            border-bottom: 3px solid #dc3545;
            padding-bottom: 15px;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            background: #dc3545;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
        }
        
        .org-info {
            text-align: center;
            flex: 1;
            margin: 0 20px;
        }
        
        .org-name {
            font-size: 18px;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 5px;
        }
        
        .org-arabic {
            font-size: 14px;
            color: #666;
            margin-bottom: 3px;
        }
        
        .org-capital {
            font-size: 10px;
            color: #666;
        }
        
        .reference {
            text-align: right;
            font-weight: bold;
            margin: 20px 0;
            font-size: 14px;
        }
        
        .title {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            margin: 30px 0;
            text-decoration: underline;
            letter-spacing: 2px;
        }
        
        .content {
            margin: 30px 0;
        }
        
        .field {
            display: flex;
            margin: 15px 0;
            padding: 8px 0;
        }
        
        .field-label {
            font-weight: bold;
            width: 180px;
            flex-shrink: 0;
        }
        
        .field-value {
            flex: 1;
            border-bottom: 1px dotted #333;
            padding-bottom: 2px;
        }
        
        .signature-section {
            margin-top: 80px;
            text-align: right;
        }
        
        .signature-location {
            margin-bottom: 40px;
        }
        
        .signature-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .signature-name {
            margin-top: 60px;
            text-decoration: underline;
            font-weight: bold;
            font-size: 14px;
        }
        
        .footer {
            position: absolute;
            bottom: 15mm;
            left: 20mm;
            right: 20mm;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        
        .controls {
            position: fixed;
            top: 10px;
            right: 10px;
            background: white;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            z-index: 1000;
        }
        
        .btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 3px;
            cursor: pointer;
            margin: 0 5px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            background: #0056b3;
        }
        
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #1e7e34; }
        
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        
        @media print {
            .controls { display: none !important; }
            .page { margin: 0; padding: 15mm; }
            body { background: white; }
        }
        
        @page {
            size: A4;
            margin: 0;
        }
    </style>
</head>
<body>
    <?php if(!isset($_GET['download'])) { ?>
    <div class="controls">
        <button onclick="window.print()" class="btn btn-success">
            🖨️ Imprimer
        </button>
        <a href="?mid=<?php echo $mid; ?>&download=1" class="btn btn-primary">
            📥 Télécharger PDF
        </a>
        <button onclick="window.close()" class="btn btn-danger">
            ❌ Fermer
        </button>
    </div>
    <?php } ?>
    
    <div class="page">
        <!-- En-tête -->
        <div class="header">
            <div class="logo">SNTP</div>
            <div class="org-info">
                <div class="org-name">SOCIÉTÉ NATIONALE DE TRAVAUX PUBLICS</div>
                <div class="org-arabic">الشركة الوطنية للأشغال العمومية</div>
                <div class="org-capital">EPE /S.P.A au Capital Social de 2.400.000.000,00 DA</div>
            </div>
            <div class="logo">SNTP</div>
        </div>
        
        <!-- Numéro de référence -->
        <div class="reference">
            Réf N°<?php echo htmlspecialchars($mission->ReferenceNumber); ?>
        </div>
        
        <!-- Titre -->
        <div class="title">
            ORDRE DE MISSION
        </div>
        
        <!-- Contenu -->
        <div class="content">
            <div class="field">
                <div class="field-label">Nom :</div>
                <div class="field-value"><?php echo htmlspecialchars($mission->NomPrenom); ?></div>
            </div>
            
            <div class="field">
                <div class="field-label">Fonction :</div>
                <div class="field-value"><?php echo htmlspecialchars($mission->UserFonction); ?></div>
            </div>
            
            <div class="field">
                <div class="field-label">Itinéraire :</div>
                <div class="field-value"><?php echo htmlspecialchars($mission->VilleDepart . ' - ' . $mission->Destinations . ' - ' . $mission->VilleDepart); ?></div>
            </div>
            
            <div class="field">
                <div class="field-label">Motif de Déplacement :</div>
                <div class="field-value"><?php echo htmlspecialchars($mission->MotifDeplacement); ?></div>
            </div>
            
            <div class="field">
                <div class="field-label">Date de Départ :</div>
                <div class="field-value"><?php echo date('d/m/Y', strtotime($mission->DateDepart)); ?></div>
            </div>
            
            <div class="field">
                <div class="field-label">Date de Retour :</div>
                <div class="field-value"><?php echo date('d/m/Y H:i', strtotime($mission->DateRetour)); ?></div>
            </div>
            
            <div class="field">
                <div class="field-label">Moyen de Transport :</div>
                <div class="field-value"><?php echo htmlspecialchars($mission->MoyenTransport); ?></div>
            </div>
        </div>
        
        <!-- Signature -->
        <div class="signature-section">
            <div class="signature-location">
                Fait à El-Hamiz, le <?php echo date('d/m/Y'); ?>
            </div>
            
            <div class="signature-title">
                La Directrice des Ressources Humaines<br>
                et des Moyens Généraux
            </div>
            
            <div class="signature-name">
                <?php echo isset($mission->ValidatorNom) ? strtoupper($mission->ValidatorNom . ' ' . $mission->ValidatorPrenom) : 'I. BOURAHLA'; ?>
            </div>
        </div>
        
        <!-- Pied de page -->
        <div class="footer">
            <div><strong>Siège Social :</strong> Route Nationale n°5 El Hamiz BP 39 - Bordj El Kiffan - Alger</div>
            <div><strong>Tél :</strong> 023.86.35.95/99 <strong>Fax :</strong> 023.86.36.03 <strong>Site Internet :</strong> www.sntp.dz</div>
        </div>
    </div>
    
    <?php if(!isset($_GET['download'])) { ?>
    <script>
        // Auto-focus pour impression
        window.onload = function() {
            setTimeout(function() {
                if(confirm('Voulez-vous imprimer cet ordre de mission maintenant ?')) {
                    window.print();
                }
            }, 500);
        }
        
        // Gestion des raccourcis clavier
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    </script>
    <?php } ?>
</body>
</html>

<?php } ?>
