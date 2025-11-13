-- Nouvelle table pour les logs d'activité
CREATE TABLE `tblactivity_logs` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `UserID` int(10) DEFAULT NULL,
  `Email` varchar(200) DEFAULT NULL,
  `Action` varchar(200) NOT NULL,
  `EntityType` varchar(100) DEFAULT NULL,
  `EntityID` int(10) DEFAULT NULL,
  `IPAddress` varchar(45) DEFAULT NULL,
  `UserAgent` text DEFAULT NULL,
  `Details` text DEFAULT NULL,
  `Status` enum('success','failure','warning') DEFAULT 'success',
  `Timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ID`),
  KEY `UserID` (`UserID`),
  KEY `idx_action` (`Action`),
  KEY `idx_timestamp` (`Timestamp`),
  KEY `idx_status` (`Status`),
  CONSTRAINT `tblactivity_logs_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `tblusers` (`ID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Nouvelle table pour les tentatives de connexion
CREATE TABLE `tbllogin_attempts` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `Email` varchar(200) NOT NULL,
  `IPAddress` varchar(45) NOT NULL,
  `AttemptTime` timestamp NOT NULL DEFAULT current_timestamp(),
  `Success` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ID`),
  KEY `idx_email_ip` (`Email`,`IPAddress`),
  KEY `idx_attempt_time` (`AttemptTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Nouvelle table pour les sessions actives
CREATE TABLE `tblactive_sessions` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `UserID` int(10) NOT NULL,
  `SessionID` varchar(128) NOT NULL,
  `IPAddress` varchar(45) DEFAULT NULL,
  `UserAgent` text DEFAULT NULL,
  `LastActivity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ID`),
  UNIQUE KEY `SessionID` (`SessionID`),
  KEY `UserID` (`UserID`),
  KEY `idx_last_activity` (`LastActivity`),
  CONSTRAINT `tblactive_sessions_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `tblusers` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Ajout de colonnes pour la sécurité des utilisateurs
ALTER TABLE `tblusers` 
ADD COLUMN `LastLogin` timestamp NULL DEFAULT NULL AFTER `RegDate`,
ADD COLUMN `LastLoginIP` varchar(45) DEFAULT NULL AFTER `LastLogin`,
ADD COLUMN `FailedLoginAttempts` int(3) DEFAULT 0 AFTER `LastLoginIP`,
ADD COLUMN `AccountLocked` tinyint(1) DEFAULT 0 AFTER `FailedLoginAttempts`,
ADD COLUMN `LockoutTime` timestamp NULL DEFAULT NULL AFTER `AccountLocked`,
ADD COLUMN `PasswordChangedAt` timestamp NULL DEFAULT NULL AFTER `LockoutTime`;

