<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
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

// Récupérer les détails de la mission avec signature du validateur
$sql = "SELECT m.*, u.Nom, u.Prenom, u.Fonction as UserFonction, u.Departement,
               v.Nom as ValidatorNom, v.Prenom as ValidatorPrenom, v.Fonction as ValidatorFonction,
               v.SignatureImage, v.SignatureType
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

// Charger TCPDF
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');

// Définir le chemin des images
$basePath = dirname(__DIR__);
$basePath = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $basePath);
define('PDF_IMAGE_PATH', $basePath . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR);
define('PDF_SIGNATURE_PATH', $basePath . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'signatures' . DIRECTORY_SEPARATOR);

// Classe personnalisée pour le PDF
class MissionPDF extends TCPDF {
    private $referenceNumber;
    
    public function setReferenceNumber($ref) {
        $this->referenceNumber = $ref;
    }
    
    // En-tête personnalisé
    public function Header() {
        $headerImage = PDF_IMAGE_PATH . 'header-sntp.jpg';
        
        if (file_exists($headerImage) && is_readable($headerImage)) {
            $this->Image($headerImage, 15, 10, 180, 0, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        } else {
            $this->SetY(15);
            $this->SetFont('helvetica', 'B', 16);
            $this->Cell(0, 10, 'SOCIÉTÉ NATIONALE DE TRAVAUX PUBLICS', 0, 1, 'C');
        }
        
        // Tél/Fax et Référence
        $this->SetY(52);
        $this->SetFont('helvetica', 'B', 10);
        $this->SetTextColor(0, 0, 0);
        
        // Tél à gauche
        $this->SetX(20);
        $this->Cell(80, 4, 'Tél. : 028-28.86.56/7948', 0, 0, 'L');
        
        // Référence à droite
        if (!empty($this->referenceNumber)) {
          $this->SetFont('helvetica', 'B', 10);
          $this->SetY(60);
          $this->Cell(0, 4, 'Réf N°' . $this->referenceNumber, 0, 1, 'R');
        } else {
            $this->Ln();
        }
        
        // Fax à gauche
        $this->SetFont('helvetica', 'B', 10);
        $this->SetY(57);
        $this->SetX(20);
        $this->Cell(0, 4, 'Fax : 028-28.82.39', 0, 1, 'L');
        
        $this->Ln(3);
    }
    
    // Pied de page personnalisé
    public function Footer() {
        $footerImage = PDF_IMAGE_PATH . 'footer-sntp.jpg';
        
        if (file_exists($footerImage) && is_readable($footerImage)) {
            $this->Image($footerImage, 15, 275, 180, 0, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        } else {
            $this->SetY(-15);
            $this->SetFont('helvetica', '', 8);
            $this->Cell(0, 5, 'Siège Social : Route Nationale n°5 El Hamiz BP 39 - Bordj El Kiffan - Alger', 0, 1, 'C');
            $this->Cell(0, 5, 'Tél : 023.86.35.95/99  Fax : 023.86.36.03  Site Internet : www.sntp.dz', 0, 1, 'C');
        }
    }
}

// Créer le document PDF
$pdf = new MissionPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Métadonnées
$pdf->SetCreator('SNTP - Gestion des Missions');
$pdf->SetAuthor('SNTP');
$pdf->SetTitle('Ordre de Mission - ' . $mission->ReferenceNumber);
$pdf->SetSubject('Ordre de Mission');

// Configurer la référence
$pdf->setReferenceNumber($mission->ReferenceNumber);

// Activer en-tête et pied de page
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);

// Marges
$pdf->SetMargins(20, 58, 20);
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(22);

// Saut de page automatique
$pdf->SetAutoPageBreak(TRUE, 25);

// Ajouter une page
$pdf->AddPage();

// TITRE: "ORDRE DE MISSION"
$pdf->SetFont('helvetica', 'BU', 20);
$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(15);
$pdf->Cell(0, 10, 'ORDRE DE MISSION', 0, 1, 'C');
$pdf->Ln(10);

// CONTENU
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(0, 0, 0);

$labelWidth = 75;
$lineHeight = 8;

$pdf->SetFont('helvetica', 'B', 14);
// Nom
$pdf->Cell($labelWidth, $lineHeight, 'Nom :', 0, 0, 'L');
$pdf->Cell(0, $lineHeight, strtoupper($mission->NomPrenom), 0, 1, 'L');
$pdf->Ln(2);

// Fonction
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell($labelWidth, $lineHeight, 'Fonction :', 0, 0, 'L');
$pdf->Cell(0, $lineHeight, $mission->UserFonction, 0, 1, 'L');
$pdf->Ln(2);

// Itinéraire
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell($labelWidth, $lineHeight, 'Itinéraire :', 0, 0, 'L');
$itineraire = strtoupper($mission->VilleDepart) . ' - ' . strtoupper(str_replace(',', ' - ', $mission->Destinations));
$pdf->Cell(0, $lineHeight, $itineraire, 0, 1, 'L');
$pdf->Ln(2);

// Motif de déplacement
$pdf->Cell($labelWidth, $lineHeight, 'Motif de Déplacement :', 0, 0, 'L');
$pdf->Cell(0, $lineHeight, $mission->MotifDeplacement, 0, 1, 'L');
$pdf->Ln(2);

// Date de départ
$pdf->Cell($labelWidth, $lineHeight, 'Date de Départ :', 0, 0, 'L');
$pdf->Cell(0, $lineHeight, date('d/m/Y', strtotime($mission->DateDepart)), 0, 1, 'L');
$pdf->Ln(2);

// Date de retour
$pdf->Cell($labelWidth, $lineHeight, 'Date de Retour :', 0, 0, 'L');
$pdf->Cell(0, $lineHeight, date('d/m/Y H:i', strtotime($mission->DateRetour)), 0, 1, 'L');
$pdf->Ln(2);

// Moyen de transport
$pdf->Cell($labelWidth, $lineHeight, 'Moyen de Transport :', 0, 0, 'L');
$pdf->Cell(0, $lineHeight, $mission->MoyenTransport, 0, 1, 'L');

// SIGNATURE
$pdf->Ln(25);
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(0, 6, 'Fait à El-Hamiz, le ' . date('d/m/Y') . '.', 0, 1, 'R');
$pdf->Ln(10);

// Titre de la fonction
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 6, 'La Directrice des Ressources Humaines', 0, 1, 'R');
$pdf->Ln(1);
$pdf->Cell(0, 6, 'et des Moyens Généraux', 0, 1, 'R');
$pdf->Ln(5);

// INSÉRER LA SIGNATURE SI DISPONIBLE
if(isset($mission->SignatureImage) && !empty($mission->SignatureImage)) {
    $signaturePath = PDF_SIGNATURE_PATH . $mission->SignatureImage;
    
    if(file_exists($signaturePath) && is_readable($signaturePath)) {
        // Position X pour aligner à droite (ajustez selon la largeur de votre signature)
        $signatureWidth = 60;  // Largeur de la signature en mm
        $pageWidth = $pdf->getPageWidth();
        $rightMargin = 20;
        $signatureX = $pageWidth - $rightMargin - $signatureWidth;
        
        // Insérer l'image de la signature
        $pdf->Image($signaturePath, $signatureX, $pdf->GetY(), $signatureWidth, 0, '', '', '', false, 300, '', false, false, 0);
        $pdf->Ln(25);
    } else {
        $pdf->Ln(15);
    }
} else {
    $pdf->Ln(15);
}

// Nom du validateur
$validatorName = 'I. BOURAHLA';
if (isset($mission->ValidatorNom) && isset($mission->ValidatorPrenom)) {
    $firstInitial = mb_substr($mission->ValidatorPrenom, 0, 1);
    $validatorName = strtoupper($firstInitial . '. ' . $mission->ValidatorNom);
}

$pdf->SetFont('helvetica', 'BU', 11);
$pdf->Cell(0, 6, $validatorName, 0, 1, 'R');

// Générer le PDF
$filename = 'ordre_mission_' . $mission->ReferenceNumber . '.pdf';

if(isset($_GET['download']) && $_GET['download'] == '1') {
    $pdf->Output($filename, 'D');
} else {
    $pdf->Output($filename, 'I');
}
?>

