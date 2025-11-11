<?php
session_start();
error_reporting(0);
include('config.php');

// Vérifier l'accès
$access_granted = false;
if(isset($_SESSION['GMSaid']) || isset($_SESSION['GMScid']) || isset($_SESSION['GMSuid'])) {
    $access_granted = true;
}

if(!$access_granted) {
    header('location:../index.php');
    exit();
}

$mid = intval($_GET['mid']);

// Récupérer les détails de la mission
$sql = "SELECT m.*, u.Nom, u.Prenom, u.Fonction as UserFonction, u.Departement,
               v.Nom as ValidatorNom, v.Prenom as ValidatorPrenom, v.Fonction as ValidatorFonction
        FROM tblmissions m 
        JOIN tblusers u ON m.UserID = u.ID 
        LEFT JOIN tblusers v ON m.ValidatedBy = v.ID
        WHERE m.ID = :mid AND m.Status = 'validee'";
$query = $dbh->prepare($sql);
$query->bindParam(':mid', $mid, PDO::PARAM_STR);
$query->execute();
$mission = $query->fetch(PDO::FETCH_OBJ);

if($query->rowCount() == 0) {
    echo "<script>alert('Mission non trouvée ou non validée !'); window.history.back();</script>";
    exit();
}

// Créer le PDF avec TCPDF (version simplifiée sans dépendance externe)
class SimplePDF {
    private $content = '';
    
    public function AddPage() {
        // En-tête HTML
        $this->content = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Ordre de Mission - ' . htmlspecialchars($mission->ReferenceNumber) . '</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
                .reference { text-align: right; font-weight: bold; margin: 20px 0; }
                .title { text-align: center; font-size: 24px; font-weight: bold; margin: 30px 0; text-decoration: underline; }
                .content { margin: 20px 0; }
                .field { margin: 10px 0; }
                .field strong { display: inline-block; width: 200px; }
                .signature { margin-top: 60px; text-align: right; }
                .footer { margin-top: 40px; text-align: center; font-size: 12px; border-top: 1px solid #ccc; padding-top: 10px; }
                @media print { 
                    body { margin: 0; }
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>';
    }
    
    public function WriteHTML($html) {
        $this->content .= $html;
    }
    
    public function Output($filename = '', $dest = '') {
        global $mission;
        
        $this->content .= '</body></html>';
        
        // Si c'est une demande de téléchargement, forcer le téléchargement
        if($dest == 'D' || isset($_GET['download'])) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            // Pour la démo, on génère un HTML qui peut être sauvegardé en PDF via le navigateur
            echo $this->content;
        } else {
            // Affichage direct pour impression
            echo $this->content;
            echo '<script>
                window.onload = function() {
                    // Auto-print si demandé
                    if(confirm("Voulez-vous imprimer cet ordre de mission ?")) {
                        window.print();
                    }
                }
            </script>';
        }
    }
}

$pdf = new SimplePDF();
$pdf->AddPage();

// Contenu du PDF basé sur l'image fournie
$html = '
<div class="header">
    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
        <tr>
            <td width="100" style="text-align: center; vertical-align: middle;">
                <div style="width: 80px; height: 80px; background: #dc3545; border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">SNTP</div>
            </td>
            <td style="text-align: center; vertical-align: middle;">
                <h2 style="margin: 0; color: #dc3545;">SOCIÉTÉ NATIONALE DE TRAVAUX PUBLICS</h2>
                <p style="margin: 5px 0; color: #666;">الشركة الوطنية للأشغال العمومية</p>
                <p style="margin: 5px 0; font-size: 12px;">EPE /S.P.A au Capital Social de 2.400.000.000,00 DA</p>
            </td>
            <td width="100" style="text-align: center; vertical-align: middle;">
                <div style="width: 80px; height: 80px; background: #dc3545; border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">SNTP</div>
            </td>
        </tr>
    </table>
</div>

<div class="reference">
    Réf N°' . htmlspecialchars($mission->ReferenceNumber) . '
</div>

<div class="title">
    ORDRE DE MISSION
</div>

<div class="content">
    <div class="field">
        <strong>Nom :</strong> ' . htmlspecialchars($mission->NomPrenom) . '
    </div>
    
    <div class="field">
        <strong>Fonction :</strong> ' . htmlspecialchars($mission->UserFonction) . '
    </div>
    
    <div class="field">
        <strong>Itinéraire :</strong> ' . htmlspecialchars($mission->VilleDepart . ' - ' . $mission->Destinations) . '
    </div>
    
    <div class="field">
        <strong>Motif de Déplacement :</strong> ' . htmlspecialchars($mission->MotifDeplacement) . '
    </div>
    
    <div class="field">
        <strong>Date de Départ :</strong> ' . date('d/m/Y', strtotime($mission->DateDepart)) . '
    </div>
    
    <div class="field">
        <strong>Date de Retour :</strong> ' . date('d/m/Y H:i', strtotime($mission->DateRetour)) . '
    </div>
    
    <div class="field">
        <strong>Moyen de Transport :</strong> ' . htmlspecialchars($mission->MoyenTransport) . '
    </div>
</div>

<div class="signature">
    <p>Fait à El-Hamiz, le ' . date('d/m/Y') . '</p>
    <br><br>
    <p><strong>La Directrice des Ressources Humaines</strong></p>
    <p><strong>et des Moyens Généraux</strong></p>
    <br><br>
    <p style="text-decoration: underline;"><strong>' . 
    (isset($mission->ValidatorNom) ? strtoupper($mission->ValidatorNom . ' ' . $mission->ValidatorPrenom) : 'I. BOURAHLA') . 
    '</strong></p>
</div>

<div class="footer">
    <p><strong>Siège Social :</strong> Route Nationale n°5 El Hamiz BP 39 - Bordj El Kiffan - Alger</p>
    <p><strong>Tél :</strong> 023.86.35.95/99 <strong>Fax :</strong> 023.86.36.03 <strong>Site Internet :</strong> www.sntp.dz</p>
</div>

<div class="no-print" style="position: fixed; top: 10px; right: 10px; background: white; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
    <button onclick="window.print()" style="background: #28a745; color: white; border: none; padding: 8px 15px; border-radius: 3px; cursor: pointer;">
        <i class="fas fa-print"></i> Imprimer
    </button>
    <button onclick="window.close()" style="background: #dc3545; color: white; border: none; padding: 8px 15px; border-radius: 3px; cursor: pointer; margin-left: 5px;">
        <i class="fas fa-times"></i> Fermer
    </button>
</div>';

$pdf->WriteHTML($html);

// Générer le fichier
$filename = 'ordre_mission_' . $mission->ReferenceNumber . '.pdf';
$pdf->Output($filename, isset($_GET['download']) ? 'D' : 'I');
?>
