<?php
// CORRECTION 1: Nettoyer le buffer et désactiver la sortie d'erreurs avant tout
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

session_start();
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

// Récupérer TOUS les détails y compris cachet et signature
$sql = "SELECT m.*, u.Nom, u.Prenom, u.Fonction as UserFonction, u.Departement,
               v.Nom as ValidatorNom, v.Prenom as ValidatorPrenom, v.Fonction as ValidatorFonction,
               v.StampImage as ValidatorStamp
        FROM tblmissions m 
        JOIN tblusers u ON m.UserID = u.ID 
        LEFT JOIN tblusers v ON m.ValidatedBy = v.ID
        WHERE m.ID = :mid AND m.Status = 'validee'";
$query = $dbh->prepare($sql);
$query->bindParam(':mid', $mid, PDO::PARAM_STR);
$query->execute();
$mission = $query->fetch(PDO::FETCH_OBJ);

if($query->rowCount() == 0) {
    ob_end_clean();
    echo "<script>alert('Mission non trouvée ou non validée !'); window.history.back();</script>";
    exit();
}

// Charger TCPDF
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');

// Définir les chemins
$basePath = dirname(__DIR__);
$basePath = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $basePath);
define('PDF_IMAGE_PATH', $basePath . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR);
define('PDF_SIGNATURE_PATH', $basePath . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'signatures' . DIRECTORY_SEPARATOR);
define('PDF_STAMP_PATH', $basePath . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'stamps' . DIRECTORY_SEPARATOR);

// CORRECTION 2: Fonction pour corriger les profils ICC des images PNG
function fixImageICCProfile($imagePath) {
    if (!file_exists($imagePath)) {
        return false;
    }
    
    $imageInfo = @getimagesize($imagePath);
    if (!$imageInfo) {
        return false;
    }
    
    $mimeType = $imageInfo['mime'];
    
    // Corriger uniquement les PNG qui causent des warnings libpng
    if ($mimeType === 'image/png') {
        try {
            $image = @imagecreatefrompng($imagePath);
            if ($image === false) {
                return false;
            }
            
            // Sauvegarder sans profil ICC
            imagealphablending($image, false);
            imagesavealpha($image, true);
            
            $tempPath = $imagePath . '.tmp';
            $result = @imagepng($image, $tempPath, 9);
            imagedestroy($image);
            
            if ($result) {
                // Remplacer l'original
                @unlink($imagePath);
                @rename($tempPath, $imagePath);
                return true;
            } else {
                @unlink($tempPath);
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }
    
    return true;
}

// Classe personnalisée
class MissionPDF extends TCPDF {
    private $referenceNumber;
    
    public function setReferenceNumber($ref) {
        $this->referenceNumber = $ref;
    }
    
    public function Header() {
        $headerImage = PDF_IMAGE_PATH . 'header-sntp.jpg';
        
        if (file_exists($headerImage) && is_readable($headerImage)) {
            $this->Image($headerImage, 15, 10, 180, 0, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        } else {
            $this->SetY(15);
            $this->SetFont('helvetica', 'B', 16);
            $this->Cell(0, 10, 'SOCIÉTÉ NATIONALE DE TRAVAUX PUBLICS', 0, 1, 'C');
        }
        
        $this->SetY(52);
        $this->SetFont('helvetica', 'B', 10);
        $this->SetTextColor(0, 0, 0);
        
        $this->SetX(20);
        $this->Cell(80, 4, 'Tél. : 028-28.86.56/7948', 0, 0, 'L');
        
        if (!empty($this->referenceNumber)) {
          $this->SetFont('helvetica', 'B', 10);
          $this->SetY(60);
          $this->Cell(0, 4, 'Réf N°' . $this->referenceNumber, 0, 1, 'R');
        } else {
            $this->Ln();
        }
        
        $this->SetFont('helvetica', 'B', 10);
        $this->SetY(57);
        $this->SetX(20);
        $this->Cell(0, 4, 'Fax : 028-28.82.39', 0, 1, 'L');
        
        $this->Ln(3);
    }
    
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

// CORRECTION 3: Corriger les profils ICC avant de créer le PDF
if(isset($mission->ValidatorStamp) && !empty($mission->ValidatorStamp)) {
    $stampPath = PDF_STAMP_PATH . $mission->ValidatorStamp;
    if(file_exists($stampPath)) {
        fixImageICCProfile($stampPath);
    }
}

if(isset($mission->SignaturePath) && !empty($mission->SignaturePath)) {
    $signaturePath = PDF_SIGNATURE_PATH . $mission->SignaturePath;
    if(file_exists($signaturePath)) {
        fixImageICCProfile($signaturePath);
    }
}

// Créer le PDF
$pdf = new MissionPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator('SNTP - Gestion des Missions');
$pdf->SetAuthor('SNTP');
$pdf->SetTitle('Ordre de Mission - ' . $mission->ReferenceNumber);
$pdf->SetSubject('Ordre de Mission');

$pdf->setReferenceNumber($mission->ReferenceNumber);
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);
$pdf->SetMargins(20, 58, 20);
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(22);
$pdf->SetAutoPageBreak(TRUE, 25);

$pdf->AddPage();

// TITRE
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

// Motif
$pdf->Cell($labelWidth, $lineHeight, 'Motif de Déplacement :', 0, 0, 'L');
$pdf->Cell(0, $lineHeight, $mission->MotifDeplacement, 0, 1, 'L');
$pdf->Ln(2);

// Date départ
$pdf->Cell($labelWidth, $lineHeight, 'Date de Départ :', 0, 0, 'L');
$pdf->Cell(0, $lineHeight, date('d/m/Y', strtotime($mission->DateDepart)), 0, 1, 'L');
$pdf->Ln(2);

// Date retour
$pdf->Cell($labelWidth, $lineHeight, 'Date de Retour :', 0, 0, 'L');
$pdf->Cell(0, $lineHeight, date('d/m/Y H:i', strtotime($mission->DateRetour)), 0, 1, 'L');
$pdf->Ln(2);

// Moyen de transport
$pdf->Cell($labelWidth, $lineHeight, 'Moyen de Transport :', 0, 0, 'L');
$pdf->Cell(0, $lineHeight, $mission->MoyenTransport, 0, 1, 'L');

// ========== SECTION SIGNATURE ==========
$pdf->Ln(17);
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(0, 6, 'Fait à El-Hamiz, le ' . date('d/m/Y') . '.', 0, 1, 'R');
$pdf->Ln(8);

// Fonction
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 6, 'La Directrice des Ressources Humaines', 0, 1, 'R');
$pdf->Ln(1);
$pdf->Cell(0, 6, 'et des Moyens Généraux', 0, 1, 'R');
$pdf->Ln(3);

// Position de départ pour les images
$pageWidth = $pdf->getPageWidth();
$rightMargin = 20;
$currentY = $pdf->GetY();

// ========== CACHET OFFICIEL (EN HAUT) ==========
$stampHeightInPDF = 0;
if(isset($mission->ValidatorStamp) && !empty($mission->ValidatorStamp)) {
    $stampPath = PDF_STAMP_PATH . $mission->ValidatorStamp;
    
    if(file_exists($stampPath) && is_readable($stampPath)) {
        $stampWidth = 55;  // Taille du cachet rond
        $stampX = $pageWidth - $rightMargin - $stampWidth;
        
        try {
            // Réduire l'opacité du cachet pour qu'il serve de fond
            $pdf->setAlpha(0.4);  // 40% d'opacité pour le cachet
            
            // Positionner le cachet
            $pdf->Image($stampPath, $stampX, $currentY, $stampWidth, 0, '', '', '', false, 300, '', false, false, 0, false, false, false);
            
            // Restaurer l'opacité complète
            $pdf->setAlpha(1);
            
            // Calculer la hauteur réelle du cachet
            list($imgWidth, $imgHeight) = getimagesize($stampPath);
            $stampHeightInPDF = ($stampWidth * $imgHeight) / $imgWidth;
        } catch(Exception $e) {
            $pdf->setAlpha(1);  // S'assurer de restaurer l'opacité en cas d'erreur
        }
    }
}

// ========== SIGNATURE MANUSCRITE (EN BAS) ==========
if(isset($mission->SignaturePath) && !empty($mission->SignaturePath)) {
    $signaturePath = PDF_SIGNATURE_PATH . $mission->SignaturePath;
    
    if(file_exists($signaturePath) && is_readable($signaturePath)) {
        $signatureWidth = 45;
        
        // Calculer la position pour centrer la signature sur le cachet
        list($sigWidth, $sigHeight) = getimagesize($signaturePath);
        $signatureHeightInPDF = ($signatureWidth * $sigHeight) / $sigWidth;
        
        // Centrer verticalement la signature sur le cachet
        $signatureY = $currentY + ($stampHeightInPDF / 2) - ($signatureHeightInPDF / 2);
        
        // Centrer horizontalement la signature sur le cachet
        $signatureX = $pageWidth - $rightMargin - 55/2 - $signatureWidth/2;
        
        try {
            // Opacité complète pour la signature (bien visible)
            $pdf->setAlpha(1.0);  // 100% d'opacité pour la signature
            
            // Superposer la signature PAR-DESSUS le cachet
            $pdf->Image($signaturePath, $signatureX, $signatureY, $signatureWidth, 0, '', '', '', false, 300, '', false, false, 1, false, false, false);
            
            // Mettre à jour currentY en fonction de la zone occupée
            $totalHeight = max($stampHeightInPDF, $signatureY - $currentY + $signatureHeightInPDF);
            $currentY += $totalHeight + 2;
        } catch(Exception $e) {
            $currentY += max($stampHeightInPDF, 20) + 2;
        }
    } else {
        $currentY += $stampHeightInPDF + 2;
    }
} else {
    $currentY += $stampHeightInPDF + 2;
}

// Nom du validateur (en dessous de tout)
$pdf->SetY($currentY);
$validatorName = 'I. BOURAHLA';
if (isset($mission->ValidatorNom) && isset($mission->ValidatorPrenom)) {
    $firstInitial = mb_substr($mission->ValidatorPrenom, 0, 1);
    $validatorName = strtoupper($firstInitial . '. ' . $mission->ValidatorNom);
}

$pdf->SetFont('helvetica', 'BU', 11);
$pdf->Cell(0, 6, $validatorName, 0, 1, 'R');

// CORRECTION 6: Nettoyer le buffer avant la génération
ob_end_clean();

// Générer le PDF
$filename = 'ordre_mission_' . $mission->ReferenceNumber . '.pdf';

if(isset($_GET['download']) && $_GET['download'] == '1') {
    $pdf->Output($filename, 'D');
} else {
    $pdf->Output($filename, 'I');
}
exit();
?>

