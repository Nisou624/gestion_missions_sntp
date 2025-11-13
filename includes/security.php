<?php
// Prévention des accès directs
if (!defined('DB_HOST')) {
    exit('Accès non autorisé');
}

class SecurityManager {
    private $dbh;
    private $maxLoginAttempts = 5;
    private $lockoutDuration = 900; // 15 minutes en secondes
    
    public function __construct($dbConnection) {
        $this->dbh = $dbConnection;
    }
    
    /**
     * Vérifier si un compte est verrouillé
     */
    public function isAccountLocked($email) {
        $sql = "SELECT AccountLocked, LockoutTime FROM tblusers WHERE Email = :email";
        $query = $this->dbh->prepare($sql);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_OBJ);
        
        if ($result && $result->AccountLocked) {
            if ($result->LockoutTime) {
                $lockoutTime = strtotime($result->LockoutTime);
                $currentTime = time();
                
                // Déverrouiller si le délai est écoulé
                if (($currentTime - $lockoutTime) > $this->lockoutDuration) {
                    $this->unlockAccount($email);
                    return false;
                }
                return true;
            }
            return true;
        }
        return false;
    }
    
    /**
     * Enregistrer une tentative de connexion
     */
    public function recordLoginAttempt($email, $ipAddress, $success) {
        $sql = "INSERT INTO tbllogin_attempts (Email, IPAddress, Success) VALUES (:email, :ip, :success)";
        $query = $this->dbh->prepare($sql);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':ip', $ipAddress, PDO::PARAM_STR);
        $query->bindParam(':success', $success, PDO::PARAM_INT);
        $query->execute();
        
        if (!$success) {
            $this->handleFailedLogin($email);
        } else {
            $this->resetFailedAttempts($email);
        }
    }
    
    /**
     * Gérer les échecs de connexion
     */
    private function handleFailedLogin($email) {
        // Incrémenter le compteur d'échecs
        $sql = "UPDATE tblusers SET FailedLoginAttempts = FailedLoginAttempts + 1 WHERE Email = :email";
        $query = $this->dbh->prepare($sql);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->execute();
        
        // Vérifier si le compte doit être verrouillé
        $sql = "SELECT FailedLoginAttempts FROM tblusers WHERE Email = :email";
        $query = $this->dbh->prepare($sql);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_OBJ);
        
        if ($result && $result->FailedLoginAttempts >= $this->maxLoginAttempts) {
            $this->lockAccount($email);
        }
    }
    
    /**
     * Verrouiller un compte
     */
    private function lockAccount($email) {
        $sql = "UPDATE tblusers SET AccountLocked = 1, LockoutTime = NOW() WHERE Email = :email";
        $query = $this->dbh->prepare($sql);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->execute();
        
        ActivityLogger::log($this->dbh, null, $email, 'account_locked', null, null, 
            'Compte verrouillé après ' . $this->maxLoginAttempts . ' tentatives échouées', 'warning');
    }
    
    /**
     * Déverrouiller un compte
     */
    private function unlockAccount($email) {
        $sql = "UPDATE tblusers SET AccountLocked = 0, FailedLoginAttempts = 0, LockoutTime = NULL WHERE Email = :email";
        $query = $this->dbh->prepare($sql);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->execute();
    }
    
    /**
     * Réinitialiser les tentatives échouées
     */
    private function resetFailedAttempts($email) {
        $sql = "UPDATE tblusers SET FailedLoginAttempts = 0, AccountLocked = 0, LockoutTime = NULL WHERE Email = :email";
        $query = $this->dbh->prepare($sql);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->execute();
    }
    
    /**
     * Obtenir le nombre de tentatives restantes
     */
    public function getRemainingAttempts($email) {
        $sql = "SELECT FailedLoginAttempts FROM tblusers WHERE Email = :email";
        $query = $this->dbh->prepare($sql);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_OBJ);
        
        if ($result) {
            return max(0, $this->maxLoginAttempts - $result->FailedLoginAttempts);
        }
        return $this->maxLoginAttempts;
    }
    
    /**
     * Créer une nouvelle session sécurisée
     */
    public function createSecureSession($userId) {
        // Régénérer l'ID de session
        session_regenerate_id(true);
        
        $sessionId = session_id();
        $ipAddress = $this->getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Enregistrer la session active
        $sql = "INSERT INTO tblactive_sessions (UserID, SessionID, IPAddress, UserAgent) 
                VALUES (:userid, :sessionid, :ip, :useragent)";
        $query = $this->dbh->prepare($sql);
        $query->bindParam(':userid', $userId, PDO::PARAM_INT);
        $query->bindParam(':sessionid', $sessionId, PDO::PARAM_STR);
        $query->bindParam(':ip', $ipAddress, PDO::PARAM_STR);
        $query->bindParam(':useragent', $userAgent, PDO::PARAM_STR);
        $query->execute();
        
        // Mettre à jour la dernière connexion
        $sql = "UPDATE tblusers SET LastLogin = NOW(), LastLoginIP = :ip WHERE ID = :userid";
        $query = $this->dbh->prepare($sql);
        $query->bindParam(':ip', $ipAddress, PDO::PARAM_STR);
        $query->bindParam(':userid', $userId, PDO::PARAM_INT);
        $query->execute();
    }
    
    /**
     * Détruire une session sécurisée
     */
    public function destroySecureSession() {
        $sessionId = session_id();
        
        if ($sessionId) {
            $sql = "DELETE FROM tblactive_sessions WHERE SessionID = :sessionid";
            $query = $this->dbh->prepare($sql);
            $query->bindParam(':sessionid', $sessionId, PDO::PARAM_STR);
            $query->execute();
        }
    }
    
    /**
     * Vérifier la validité d'une session
     */
    public function validateSession($userId) {
        $sessionId = session_id();
        
        $sql = "SELECT ID FROM tblactive_sessions 
                WHERE UserID = :userid AND SessionID = :sessionid 
                AND LastActivity > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        $query = $this->dbh->prepare($sql);
        $query->bindParam(':userid', $userId, PDO::PARAM_INT);
        $query->bindParam(':sessionid', $sessionId, PDO::PARAM_STR);
        $query->execute();
        
        if ($query->rowCount() > 0) {
            // Mettre à jour LastActivity
            $sql = "UPDATE tblactive_sessions SET LastActivity = NOW() 
                    WHERE UserID = :userid AND SessionID = :sessionid";
            $query = $this->dbh->prepare($sql);
            $query->bindParam(':userid', $userId, PDO::PARAM_INT);
            $query->bindParam(':sessionid', $sessionId, PDO::PARAM_STR);
            $query->execute();
            return true;
        }
        return false;
    }
    
    /**
     * Nettoyer les anciennes sessions
     */
    public function cleanupOldSessions() {
        $sql = "DELETE FROM tblactive_sessions WHERE LastActivity < DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        $query = $this->dbh->prepare($sql);
        $query->execute();
    }
    
    /**
     * Obtenir l'adresse IP du client
     */
    public function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        }
    }
    
    /**
     * Valider la force d'un mot de passe
     */
    public function validatePasswordStrength($password) {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une lettre majuscule";
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une lettre minuscule";
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins un chiffre";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}

class ActivityLogger {
    /**
     * Enregistrer une activité
     */
    public static function log($dbh, $userId, $email, $action, $entityType = null, $entityId = null, $details = null, $status = 'success') {
        $ipAddress = self::getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $sql = "INSERT INTO tblactivity_logs (UserID, Email, Action, EntityType, EntityID, IPAddress, UserAgent, Details, Status) 
                VALUES (:userid, :email, :action, :entitytype, :entityid, :ip, :useragent, :details, :status)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':userid', $userId, PDO::PARAM_INT);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':action', $action, PDO::PARAM_STR);
        $query->bindParam(':entitytype', $entityType, PDO::PARAM_STR);
        $query->bindParam(':entityid', $entityId, PDO::PARAM_INT);
        $query->bindParam(':ip', $ipAddress, PDO::PARAM_STR);
        $query->bindParam(':useragent', $userAgent, PDO::PARAM_STR);
        $query->bindParam(':details', $details, PDO::PARAM_STR);
        $query->bindParam(':status', $status, PDO::PARAM_STR);
        $query->execute();
    }
    
    private static function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        }
    }
}
?>

