-- phpMyAdmin SQL Dump
-- Système de Gestion des Ordres de Mission
-- Base de données: gestion_missions_db

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Base de données: `gestion_missions_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `tblusers`
--

CREATE TABLE `tblusers` (
  `ID` int(10) NOT NULL,
  `Nom` varchar(200) DEFAULT NULL,
  `Prenom` varchar(200) DEFAULT NULL,
  `Email` varchar(200) DEFAULT NULL,
  `Fonction` varchar(200) DEFAULT NULL,
  `Departement` varchar(200) DEFAULT NULL,
  `MobileNumber` bigint(10) DEFAULT NULL,
  `Password` varchar(200) NOT NULL,
  `Role` enum('admin','chef_departement','user') DEFAULT 'user',
  `RegDate` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `tblusers`
--

INSERT INTO `tblusers` (`ID`, `Nom`, `Prenom`, `Email`, `Fonction`, `Departement`, `MobileNumber`, `Password`, `Role`, `RegDate`) VALUES
(1, 'Dubois', 'Marie', 'admin@sntp.dz', 'Directrice des Ressources Humaines', 'Direction Générale', 213555000001, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2025-01-01 08:00:00'),
(2, 'Bourahla', 'Imène', 'i.bourahla@sntp.dz', 'Chef de Département Audit', 'Audit et Contrôle', 213555000002, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'chef_departement', '2025-01-01 08:00:00'),
(3, 'Temmar', 'Arezki', 'a.temmar@sntp.dz', 'Auditeur en Chef', 'Audit et Contrôle', 213555000003, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '2025-01-01 08:00:00'),
(4, 'Benali', 'Karim', 'k.benali@sntp.dz', 'Ingénieur Travaux', 'Travaux Publics', 213555000004, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '2025-01-01 08:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `tblmissions`
--

CREATE TABLE `tblmissions` (
  `ID` int(10) NOT NULL,
  `UserID` int(5) NOT NULL,
  `ReferenceNumber` varchar(50) DEFAULT NULL,
  `NomPrenom` varchar(400) DEFAULT NULL,
  `Fonction` varchar(200) DEFAULT NULL,
  `VilleDepart` varchar(200) DEFAULT NULL,
  `DateDepart` date DEFAULT NULL,
  `Destinations` text DEFAULT NULL,
  `ItineraireType` varchar(100) DEFAULT NULL,
  `MotifDeplacement` varchar(300) DEFAULT NULL,
  `MoyenTransport` varchar(200) DEFAULT NULL,
  `DateRetour` date DEFAULT NULL,
  `Observations` text DEFAULT NULL,
  `Status` enum('en_attente','validee','rejetee','en_cours') DEFAULT 'en_attente',
  `Remarque` text DEFAULT NULL,
  `ValidatedBy` int(5) DEFAULT NULL,
  `DateCreation` timestamp NULL DEFAULT current_timestamp(),
  `DateValidation` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `tblmissions`
--

INSERT INTO `tblmissions` (`ID`, `UserID`, `ReferenceNumber`, `NomPrenom`, `Fonction`, `VilleDepart`, `DateDepart`, `Destinations`, `ItineraireType`, `MotifDeplacement`, `MoyenTransport`, `DateRetour`, `Observations`, `Status`, `Remarque`, `ValidatedBy`, `DateCreation`, `DateValidation`) VALUES
(1, 3, '2025-001', 'TEMMAR Arezki', 'Auditeur en Chef', 'Alger', '2025-01-15', 'El-Oued, Djanet', 'Circuit avec escales', 'Audit technique des installations', 'Véhicule de service', '2025-01-20', "Mission d'audit des nouvelles installations", 'validee', 'Mission approuvée par la direction', 2, '2025-01-10 08:00:00', '2025-01-12 09:30:00'),
(2, 4, '2025-002', 'BENALI Karim', 'Ingénieur Travaux', 'Alger', '2025-01-25', 'Oran', 'Aller-retour', 'Formation technique', 'Avion', '2025-01-27', 'Formation sur nouveaux équipements', 'en_attente', NULL, NULL, '2025-01-20 10:15:00', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `tblmission_destinations`
--

CREATE TABLE `tblmission_destinations` (
  `ID` int(10) NOT NULL,
  `MissionID` int(10) NOT NULL,
  `Ville` varchar(200) NOT NULL,
  `Ordre` int(3) DEFAULT NULL,
  `Type` enum('depart','escale','arrivee') DEFAULT 'escale'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `tblmission_destinations`
--

INSERT INTO `tblmission_destinations` (`ID`, `MissionID`, `Ville`, `Ordre`, `Type`) VALUES
(1, 1, 'Alger', 1, 'depart'),
(2, 1, 'El-Oued', 2, 'escale'),
(3, 1, 'Djanet', 3, 'escale'),
(4, 1, 'Alger', 4, 'arrivee'),
(5, 2, 'Alger', 1, 'depart'),
(6, 2, 'Oran', 2, 'arrivee');

-- --------------------------------------------------------

--
-- Structure de la table `tblmission_validations`
--

CREATE TABLE `tblmission_validations` (
  `ID` int(10) NOT NULL,
  `MissionID` int(10) NOT NULL,
  `ValidatorID` int(10) NOT NULL,
  `Action` enum('validee','rejetee','modification_demandee') NOT NULL,
  `Commentaire` text DEFAULT NULL,
  `SignaturePath` varchar(500) DEFAULT NULL,
  `ValidatedAt` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `tblmission_validations`
--

INSERT INTO `tblmission_validations` (`ID`, `MissionID`, `ValidatorID`, `Action`, `Commentaire`, `SignaturePath`, `ValidatedAt`) VALUES
(1, 1, 2, 'validee', 'Mission approuvée conformément aux procédures', 'assets/signatures/signature_bourahla.png', '2025-01-12 09:30:00');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `tblusers`
--
ALTER TABLE `tblusers`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Index pour la table `tblmissions`
--
ALTER TABLE `tblmissions`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `ValidatedBy` (`ValidatedBy`);

--
-- Index pour la table `tblmission_destinations`
--
ALTER TABLE `tblmission_destinations`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `MissionID` (`MissionID`);

--
-- Index pour la table `tblmission_validations`
--
ALTER TABLE `tblmission_validations`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `MissionID` (`MissionID`),
  ADD KEY `ValidatorID` (`ValidatorID`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `tblusers`
--
ALTER TABLE `tblusers`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `tblmissions`
--
ALTER TABLE `tblmissions`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `tblmission_destinations`
--
ALTER TABLE `tblmission_destinations`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `tblmission_validations`
--
ALTER TABLE `tblmission_validations`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `tblmissions`
--
ALTER TABLE `tblmissions`
  ADD CONSTRAINT `tblmissions_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `tblusers` (`ID`),
  ADD CONSTRAINT `tblmissions_ibfk_2` FOREIGN KEY (`ValidatedBy`) REFERENCES `tblusers` (`ID`);

--
-- Contraintes pour la table `tblmission_destinations`
--
ALTER TABLE `tblmission_destinations`
  ADD CONSTRAINT `tblmission_destinations_ibfk_1` FOREIGN KEY (`MissionID`) REFERENCES `tblmissions` (`ID`) ON DELETE CASCADE;

--
-- Contraintes pour la table `tblmission_validations`
--
ALTER TABLE `tblmission_validations`
  ADD CONSTRAINT `tblmission_validations_ibfk_1` FOREIGN KEY (`MissionID`) REFERENCES `tblmissions` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblmission_validations_ibfk_2` FOREIGN KEY (`ValidatorID`) REFERENCES `tblusers` (`ID`);

COMMIT;
