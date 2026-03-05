-- Extra tables for TRANSLOG AGENCY MANAGER

-- Table for Expenses
CREATE TABLE IF NOT EXISTS `depenses` (
  `idDepense` int NOT NULL AUTO_INCREMENT,
  `libelle` varchar(255) NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `date_depense` date NOT NULL,
  `idAgenc` int DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idDepense`),
  FOREIGN KEY (`idAgenc`) REFERENCES `agence` (`idAg`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table for Services (if not already handled by Courrier/Trajet)
CREATE TABLE IF NOT EXISTS `services_extra` (
  `idServ` int NOT NULL AUTO_INCREMENT,
  `nom_service` varchar(150) NOT NULL,
  `description` text,
  `prix` decimal(10,2) NOT NULL,
  PRIMARY KEY (`idServ`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Ensure default admin exists with hashed password
-- Password: Admin@123
-- Result of password_hash('Admin@123', PASSWORD_DEFAULT)
-- For the sake of this setup, we'll use a known hash. 
-- Note: In the provided SQL, there's already an 'admin' role user. We'll update it or add a new one.

INSERT INTO `utilisateur` (`nom`, `prenom`, `email`, `genre`, `password`, `role`, `statut`) 
VALUES ('Manager', 'Admin', 'admin@translog.com', 'M', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'actif')
ON DUPLICATE KEY UPDATE `password` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', `role` = 'admin', `statut` = 'actif';
