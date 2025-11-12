-- Supprimer les anciennes colonnes de signature des utilisateurs
-- (on signe à chaque validation maintenant)
ALTER TABLE tblusers DROP COLUMN IF EXISTS SignatureImage;
ALTER TABLE tblusers DROP COLUMN IF EXISTS SignatureType;
ALTER TABLE tblusers DROP COLUMN IF EXISTS SignatureDate;

-- Ajouter une colonne pour le cachet officiel (une seule image par chef)
ALTER TABLE tblusers ADD COLUMN IF NOT EXISTS OfficialStamp VARCHAR(255) DEFAULT NULL;
ALTER TABLE tblusers ADD COLUMN IF NOT EXISTS StampUploadDate DATETIME DEFAULT NULL;

-- S'assurer que SignaturePath existe dans tblmissions
ALTER TABLE tblmissions ADD COLUMN IF NOT EXISTS SignaturePath VARCHAR(255) DEFAULT NULL;

-- Afficher les modifications
SELECT 'Colonnes mises à jour avec succès' AS Status;

