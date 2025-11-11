-- Ajout des colonnes pour les signatures dans tblusers
ALTER TABLE tblusers 
ADD COLUMN IF NOT EXISTS SignatureImage VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS SignatureType ENUM('drawn', 'uploaded') DEFAULT 'drawn',
ADD COLUMN IF NOT EXISTS SignatureDate DATETIME DEFAULT NULL;

-- Ajout de la colonne pour stocker la signature dans tblmissions
ALTER TABLE tblmissions 
ADD COLUMN IF NOT EXISTS SignaturePath VARCHAR(255) DEFAULT NULL;

-- Afficher les colonnes pour vérifier
SHOW COLUMNS FROM tblusers WHERE Field LIKE 'Signature%';
SHOW COLUMNS FROM tblmissions WHERE Field = 'SignaturePath';

