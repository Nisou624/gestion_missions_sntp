-- Ajouter les colonnes de signature dans la table tblusers
ALTER TABLE tblusers ADD COLUMN IF NOT EXISTS SignatureImage VARCHAR(255) DEFAULT NULL;
ALTER TABLE tblusers ADD COLUMN IF NOT EXISTS SignatureType ENUM('drawn', 'uploaded') DEFAULT 'drawn';
ALTER TABLE tblusers ADD COLUMN IF NOT EXISTS SignatureDate DATETIME DEFAULT NULL;

-- Ajouter une colonne pour stocker la signature dans tblmissions
ALTER TABLE tblmissions ADD COLUMN IF NOT EXISTS SignaturePath VARCHAR(255) DEFAULT NULL;

