<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connexion à la base de données
try {
    $dbh = new PDO("mysql:host=localhost;dbname=gestion_missions_db;charset=utf8", "root", "", array(
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ));
} catch (PDOException $e) {
    die("Erreur de connexion: " . $e->getMessage());
}

if (!isset($_SESSION['GMSuid']) || strlen($_SESSION['GMSuid']) == 0) {
    header('location:logout.php');
    exit();
}

// Fonction pour générer le numéro de référence
function generateReferenceNumber() {
    global $dbh;
    $year = date('Y');
    $month = date('m');
    $sequence = 1;
    
    try {
        $sql = "SELECT MAX(CAST(SUBSTRING(ReferenceNumber, 8) AS UNSIGNED)) as max_seq 
                FROM tblmissions 
                WHERE ReferenceNumber LIKE :pattern";
        $query = $dbh->prepare($sql);
        $pattern = $year . '-' . $month . '-%';
        $query->bindParam(':pattern', $pattern, PDO::PARAM_STR);
        $query->execute();
        
        if ($query->rowCount() > 0) {
            $result = $query->fetch(PDO::FETCH_OBJ);
            $sequence = ($result->max_seq ?? 0) + 1;
        }
        
        return $year . '-' . $month . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    } catch(Exception $e) {
        return $year . '-' . $month . '-001';
    }
}

// Données pour les listes
$motifs_mission = array(
    'Formation professionnelle',
    'Audit et inspection',
    'Réunion de coordination',
    'Mission technique',
    'Supervision de chantier',
    'Représentation officielle',
    'Expertise technique',
    'Autre'
);

$moyens_transport = array(
    'Véhicule de service',
    'Transport en commun',
    'Avion',
    'Train',
    'Véhicule personnel'
);

// Traitement du formulaire
if(isset($_POST['submit'])) {
    try {
        $submitted_by = $_SESSION['GMSuid']; // L'utilisateur connecté qui soumet
        $employee_id = intval($_POST['employee_id']); // L'employé qui va en mission
        $ville_depart = trim($_POST['ville_depart']);
        $date_depart = $_POST['date_depart'];
        $destinations = trim($_POST['destinations']);
        $itineraire_type = $_POST['itineraire_type'];
        $motif = $_POST['motif'];
        $moyen_transport = $_POST['moyen_transport'];
        $date_retour = $_POST['date_retour'];
        $observations = trim($_POST['observations']);
        
        // Validations
        if($employee_id <= 0) {
            throw new Exception("Veuillez sélectionner un employé valide !");
        }
        
        if(empty($ville_depart) || empty($destinations) || empty($itineraire_type) || empty($motif) || empty($moyen_transport)) {
            throw new Exception("Tous les champs obligatoires doivent être remplis !");
        }
        
        if($date_retour <= $date_depart) {
            throw new Exception("La date de retour doit être postérieure à la date de départ !");
        }
        
        // Récupérer les infos de l'employé qui va en mission
        $sql = "SELECT Nom, Prenom, Fonction FROM tblusers WHERE ID = :employee_id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
        $query->execute();
        $employee = $query->fetch(PDO::FETCH_OBJ);
        
        if(!$employee) {
            throw new Exception("Employé sélectionné non trouvé !");
        }
        
        $reference = generateReferenceNumber();
        $nom_prenom = $employee->Nom . ' ' . $employee->Prenom;
        $fonction = $employee->Fonction;
        
        // Insertion avec SubmittedBy
        $sql = "INSERT INTO tblmissions (
            UserID, SubmittedBy, ReferenceNumber, NomPrenom, Fonction, 
            VilleDepart, DateDepart, Destinations, ItineraireType, 
            MotifDeplacement, MoyenTransport, DateRetour, Observations, 
            Status, DateCreation
        ) VALUES (
            :employee_id, :submitted_by, :reference, :nom_prenom, :fonction,
            :ville_depart, :date_depart, :destinations, :itineraire_type,
            :motif, :moyen_transport, :date_retour, :observations,
            'en_attente', NOW()
        )";
        
        $query = $dbh->prepare($sql);
        $query->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
        $query->bindParam(':submitted_by', $submitted_by, PDO::PARAM_INT);
        $query->bindParam(':reference', $reference, PDO::PARAM_STR);
        $query->bindParam(':nom_prenom', $nom_prenom, PDO::PARAM_STR);
        $query->bindParam(':fonction', $fonction, PDO::PARAM_STR);
        $query->bindParam(':ville_depart', $ville_depart, PDO::PARAM_STR);
        $query->bindParam(':date_depart', $date_depart, PDO::PARAM_STR);
        $query->bindParam(':destinations', $destinations, PDO::PARAM_STR);
        $query->bindParam(':itineraire_type', $itineraire_type, PDO::PARAM_STR);
        $query->bindParam(':motif', $motif, PDO::PARAM_STR);
        $query->bindParam(':moyen_transport', $moyen_transport, PDO::PARAM_STR);
        $query->bindParam(':date_retour', $date_retour, PDO::PARAM_STR);
        $query->bindParam(':observations', $observations, PDO::PARAM_STR);
        
        if($query->execute()) {
            $lastId = $dbh->lastInsertId();
            if ($lastId > 0) {
                $_SESSION['success_message'] = "Demande de mission créée avec succès ! Référence: " . $reference . " pour " . $nom_prenom;
                header('location: my-missions.php');
                exit();
            }
        }
        
    } catch(Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle Demande de Mission - Système de Gestion des Missions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
</head>
<body>
    <?php include_once('includes/sidebar.php'); ?>
    
    <div class="main-content">
        <?php include_once('includes/header.php'); ?>
        
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-plus-circle"></i> Nouvelle Demande de Mission</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Accueil</a></li>
                        <li class="breadcrumb-item active">Nouvelle Demande</li>
                    </ol>
                </nav>
            </div>
            
            <?php if(isset($error_message)) { ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Erreur !</strong> <?php echo htmlentities($error_message); ?>
            </div>
            <?php } ?>
            
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-form"></i> Formulaire de Demande d'Ordre de Mission</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" id="missionForm">
                                <!-- Information sur qui soumet -->
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Information :</strong> Vous soumettez cette demande en tant que 
                                    <?php 
                                    $sql = "SELECT Nom, Prenom, Fonction FROM tblusers WHERE ID = :uid";
                                    $query = $dbh->prepare($sql);
                                    $query->bindParam(':uid', $_SESSION['GMSuid'], PDO::PARAM_INT);
                                    $query->execute();
                                    $current_user = $query->fetch(PDO::FETCH_OBJ);
                                    echo "<strong>" . htmlentities($current_user->Nom . ' ' . $current_user->Prenom) . "</strong> (" . htmlentities($current_user->Fonction) . ")";
                                    ?>
                                </div>
                                
                                <!-- Sélection de l'employé -->
                                <div class="row">
                                    <div class="col-12">
                                        <h6 class="text-primary border-bottom pb-2 mb-3">
                                            <i class="fas fa-user"></i> Employé à Envoyer en Mission
                                        </h6>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="employee_id">Sélectionner l'employé à envoyer <span class="text-danger">*</span></label>
                                            <select class="form-control select2" id="employee_id" name="employee_id" required>
                                                <option value="">-- Sélectionner un employé --</option>
                                                <?php
                                                $sql = "SELECT ID, Nom, Prenom, Fonction, Departement FROM tblusers WHERE Role IN ('user', 'chef_departement') ORDER BY Nom, Prenom";
                                                $query = $dbh->prepare($sql);
                                                $query->execute();
                                                $employees = $query->fetchAll(PDO::FETCH_OBJ);
                                                
                                                foreach($employees as $emp) {
                                                    $display_name = htmlentities($emp->Nom . ' ' . $emp->Prenom . ' - ' . $emp->Fonction . ' (' . $emp->Departement . ')');
                                                    $selected = ($emp->ID == $_SESSION['GMSuid']) ? 'selected' : ''; // Pré-sélectionner l'utilisateur connecté
                                                    echo '<option value="'.$emp->ID.'" data-fonction="'.htmlentities($emp->Fonction).'" data-dept="'.htmlentities($emp->Departement).'" data-nom="'.htmlentities($emp->Nom . ' ' . $emp->Prenom).'" '.$selected.'>'.$display_name.'</option>';
                                                }
                                                ?>
                                            </select>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-lightbulb"></i> 
                                                Vous pouvez créer une demande pour vous-même ou pour un collègue
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Aperçu de l'Employé</label>
                                            <div id="employee_preview" class="form-control-plaintext border rounded p-3 bg-light">
                                                <small class="text-muted">Sélectionnez un employé</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Détails du déplacement -->
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <h6 class="text-primary border-bottom pb-2 mb-3">
                                            <i class="fas fa-map-marker-alt"></i> Détails du Déplacement
                                        </h6>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="ville_depart">Ville de Départ <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="ville_depart" name="ville_depart" 
                                                   list="villes_algerie" placeholder="Ex: Alger" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="date_depart">Date de Départ <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="date_depart" name="date_depart" 
                                                   required min="<?php echo date('Y-m-d'); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="destinations">Destinations <span class="text-danger">*</span></label>
                                            <textarea class="form-control" id="destinations" name="destinations" rows="3" 
                                                    placeholder="Ex: Oran, Constantine, Annaba" required></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="itineraire_type">Type d'Itinéraire <span class="text-danger">*</span></label>
                                            <select class="form-control" id="itineraire_type" name="itineraire_type" required>
                                                <option value="">-- Sélectionner --</option>
                                                <option value="Aller simple">Aller simple</option>
                                                <option value="Aller-retour">Aller-retour</option>
                                                <option value="Circuit avec escales">Circuit avec escales</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="motif">Motif du Déplacement <span class="text-danger">*</span></label>
                                            <select class="form-control" id="motif" name="motif" required>
                                                <option value="">-- Sélectionner --</option>
                                                <?php foreach($motifs_mission as $motif_option) { ?>
                                                <option value="<?php echo htmlentities($motif_option); ?>"><?php echo htmlentities($motif_option); ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="moyen_transport">Moyen de Transport <span class="text-danger">*</span></label>
                                            <select class="form-control" id="moyen_transport" name="moyen_transport" required>
                                                <option value="">-- Sélectionner --</option>
                                                <?php foreach($moyens_transport as $transport) { ?>
                                                <option value="<?php echo htmlentities($transport); ?>"><?php echo htmlentities($transport); ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="date_retour">Date de Retour <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="date_retour" name="date_retour" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="observations">Observations</label>
                                            <textarea class="form-control" id="observations" name="observations" rows="3" 
                                                    placeholder="Informations supplémentaires..."></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group text-center mt-4">
                                    <button type="submit" name="submit" class="btn btn-primary btn-lg px-5">
                                        <i class="fas fa-paper-plane"></i> Soumettre la Demande
                                    </button>
                                    <a href="dashboard.php" class="btn btn-secondary btn-lg px-5 ml-3">
                                        <i class="fas fa-times"></i> Annuler
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Liste des villes algériennes -->
    <datalist id="villes_algerie">
        <option value="Alger"><option value="Oran"><option value="Constantine"><option value="Annaba">
        <option value="Sétif"><option value="Biskra"><option value="Tlemcen"><option value="Béjaïa">
        <option value="Mostaganem"><option value="El Hamiz"><option value="Bordj El Kiffan">
    </datalist>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: 'Rechercher un employé...',
            width: '100%'
        });
        
        // Aperçu employé
        $('#employee_id').change(function() {
            var option = $(this).find('option:selected');
            var nom = option.data('nom');
            var fonction = option.data('fonction');
            var dept = option.data('dept');
            
            if($(this).val()) {
                $('#employee_preview').html(
                    '<strong>👤 ' + nom + '</strong><br>' +
                    '<strong>💼</strong> ' + fonction + '<br>' +
                    '<strong>🏢</strong> ' + dept
                );
            } else {
                $('#employee_preview').html('<small class="text-muted">Sélectionnez un employé</small>');
            }
        });
        
        // Validation dates
        $('#date_depart').change(function() {
            var dateDepart = new Date($(this).val());
            var minRetour = new Date(dateDepart);
            minRetour.setDate(minRetour.getDate() + 1);
            $('#date_retour').attr('min', minRetour.toISOString().split('T')[0]);
        });
        
        // Déclencher l'aperçu si un employé est déjà sélectionné
        if($('#employee_id').val()) {
            $('#employee_id').trigger('change');
        }
    });
    </script>
</body>
</html>

<?php ?>
