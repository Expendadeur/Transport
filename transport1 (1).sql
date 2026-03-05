-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : mer. 04 mars 2026 à 13:09
-- Version du serveur : 8.0.44
-- Version de PHP : 8.4.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `transport1`
--

-- --------------------------------------------------------

--
-- Structure de la table `adresse`
--

CREATE TABLE `adresse` (
  `idAdr` int NOT NULL,
  `pays` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `province` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `commune` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `zone` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `quartier` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `avenue` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `numero` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `adresse`
--

INSERT INTO `adresse` (`idAdr`, `pays`, `province`, `commune`, `zone`, `quartier`, `avenue`, `numero`) VALUES
(1, 'Burundi', 'gitega', 'Buhiga', 'murere', 'tenga', 'mwakiro', '30'),
(2, 'Burundi', 'burunga', 'buhiga', 'mpanda', 'bukirasazi', 'nyambuye', '16'),
(4, 'Burundi', 'burunga', 'Buhiga', 'murere', 'musinzira', 'gasasa', '20');

-- --------------------------------------------------------

--
-- Structure de la table `agence`
--

CREATE TABLE `agence` (
  `idAg` int NOT NULL,
  `nom_agence` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `tel_agence` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_vil` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `agence`
--

INSERT INTO `agence` (`idAg`, `nom_agence`, `tel_agence`, `id_vil`) VALUES
(1, 'Ngenzi', '67829938', 1),
(2, 'Buragane', '222233344', 2);

-- --------------------------------------------------------

--
-- Structure de la table `automobile`
--

CREATE TABLE `automobile` (
  `id_aut` int NOT NULL,
  `marque` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `modele` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `immatriculation` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `capacite` int DEFAULT NULL,
  `etat` enum('en service','en maintenance') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_agenc` int DEFAULT NULL,
  `id_Trajet` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `courrier`
--

CREATE TABLE `courrier` (
  `idCourrier` int NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `expediteur` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `destinateur` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `agence_depart` int DEFAULT NULL,
  `agence_arrive` int DEFAULT NULL,
  `date_expedition` datetime DEFAULT NULL,
  `date_reception` datetime DEFAULT NULL,
  `poids` decimal(8,2) DEFAULT NULL,
  `prix` decimal(10,2) DEFAULT NULL,
  `statut` enum('en transit','livré','retourné') COLLATE utf8mb4_general_ci DEFAULT 'en transit',
  `code_suivi` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `type_courrier` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `signature_reception` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déclencheurs `courrier`
--
DELIMITER $$
CREATE TRIGGER `before_insert_courrier` BEFORE INSERT ON `courrier` FOR EACH ROW BEGIN
    IF NEW.code_suivi IS NULL THEN
        SET NEW.code_suivi =
        CONCAT('CR', LPAD(FLOOR(RAND()*100000000),8,'0'));
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `depart`
--

CREATE TABLE `depart` (
  `idDep` int NOT NULL,
  `id_Trajet` int DEFAULT NULL,
  `idHoraire` int DEFAULT NULL,
  `idAuto` int DEFAULT NULL,
  `date_depart` date DEFAULT NULL,
  `places_disponibles` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `horaire_trajet`
--

CREATE TABLE `horaire_trajet` (
  `idHoraire` int NOT NULL,
  `date_depart` date DEFAULT NULL,
  `heure_depart` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `passager`
--

CREATE TABLE `passager` (
  `idP` int NOT NULL,
  `nomP` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `prenomP` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `genre` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `age` int DEFAULT NULL,
  `telephone` int NOT NULL,
  `id_Adres` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `passager`
--

INSERT INTO `passager` (`idP`, `nomP`, `prenomP`, `genre`, `age`, `telephone`, `id_Adres`) VALUES
(1, 'muhimpundu', 'ange', 'Femme', 25, 0, 1),
(2, 'karikurubu', 'rene', 'Homme', 34, 0, 2),
(3, 'bukuru', 'mathieu', 'Masculin', 30, 68, 4);

-- --------------------------------------------------------

--
-- Structure de la table `payment`
--

CREATE TABLE `payment` (
  `idPay` int NOT NULL,
  `montant` decimal(10,2) DEFAULT NULL,
  `date_payment` date DEFAULT NULL,
  `mode_payment` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `statut` enum('validé','en attente','annulé') COLLATE utf8mb4_general_ci DEFAULT 'en attente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `reservation`
--

CREATE TABLE `reservation` (
  `idRes` int NOT NULL,
  `date_reservation` date DEFAULT NULL,
  `nr_place` int DEFAULT NULL,
  `statut` enum('validee','annulee') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_Agence` int DEFAULT NULL,
  `id_Passager` int DEFAULT NULL,
  `id_Trajet` int DEFAULT NULL,
  `id_Payment` int DEFAULT NULL,
  `numero_reservation` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_annulation` datetime DEFAULT NULL,
  `motif_annulation` text COLLATE utf8mb4_general_ci,
  `prix_total` decimal(10,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déclencheurs `reservation`
--
DELIMITER $$
CREATE TRIGGER `before_insert_reservation` BEFORE INSERT ON `reservation` FOR EACH ROW BEGIN
    IF NEW.numero_reservation IS NULL THEN
        SET NEW.numero_reservation =
        CONCAT('RES', LPAD(FLOOR(RAND()*1000000),6,'0'));
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `trajet`
--

CREATE TABLE `trajet` (
  `id_Traj` int NOT NULL,
  `ville_depart` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ville_arrive` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `distance` decimal(8,2) DEFAULT NULL,
  `prix` decimal(10,2) DEFAULT NULL,
  `id_Horaire` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `trajet`
--

INSERT INTO `trajet` (`id_Traj`, `ville_depart`, `ville_arrive`, `distance`, `prix`, `id_Horaire`) VALUES
(1, 'bujumbura', 'gitega', 65.00, 12000.00, NULL),
(2, 'bujumbura', 'nyanzalac', 11.00, 9000.00, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `idUt` int NOT NULL,
  `nom` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `prenom` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `genre` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` enum('admin','gestionnaire','agent','chauffeur') COLLATE utf8mb4_general_ci NOT NULL,
  `idAdres` int DEFAULT NULL,
  `idAgenc` int DEFAULT NULL,
  `statut` enum('actif','inactif') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'actif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`idUt`, `nom`, `prenom`, `email`, `genre`, `password`, `role`, `idAdres`, `idAgenc`, `statut`) VALUES
(2, 'nihorimbere', 'desire', 'desire@gmail.com', 'M', 'desire123', 'admin', NULL, NULL, 'inactif'),
(3, 'nduwayo', 'salvator', 'salvator@gmail.com', 'M', 'salvat123', 'gestionnaire', NULL, NULL, 'actif'),
(4, 'ndikuriyo', 'ferdinand', 'ferdinand@gmail.com', 'M', 'ferdinand345', 'agent', NULL, NULL, 'actif'),
(5, 'niyongere', 'chantal', 'chantal@gmail.com', 'F', 'chantal45', 'gestionnaire', NULL, NULL, 'actif'),
(6, 'Nduwarugira', 'Thierry', 'thierry@gmail.com', 'M', 'thierry60', 'agent', NULL, NULL, 'actif'),
(8, 'ngabireyimana', 'vanessa', 'vanessa@gmail.com', 'F', 'vanbe100', 'chauffeur', NULL, NULL, 'actif');

-- --------------------------------------------------------

--
-- Structure de la table `ville`
--

CREATE TABLE `ville` (
  `idVil` int NOT NULL,
  `nom` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `station` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_adres` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `ville`
--

INSERT INTO `ville` (`idVil`, `nom`, `station`, `id_adres`) VALUES
(1, 'bujumbura', 'cotebu', NULL),
(2, 'Musaga', 'Kwisoko', NULL);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `adresse`
--
ALTER TABLE `adresse`
  ADD PRIMARY KEY (`idAdr`);

--
-- Index pour la table `agence`
--
ALTER TABLE `agence`
  ADD PRIMARY KEY (`idAg`),
  ADD KEY `id_vil` (`id_vil`);

--
-- Index pour la table `automobile`
--
ALTER TABLE `automobile`
  ADD PRIMARY KEY (`id_aut`),
  ADD KEY `id_agenc` (`id_agenc`),
  ADD KEY `id_Trajet` (`id_Trajet`);

--
-- Index pour la table `courrier`
--
ALTER TABLE `courrier`
  ADD PRIMARY KEY (`idCourrier`),
  ADD UNIQUE KEY `code_suivi` (`code_suivi`),
  ADD KEY `agence_depart` (`agence_depart`),
  ADD KEY `agence_arrive` (`agence_arrive`);

--
-- Index pour la table `depart`
--
ALTER TABLE `depart`
  ADD PRIMARY KEY (`idDep`),
  ADD KEY `id_Trajet` (`id_Trajet`),
  ADD KEY `idHoraire` (`idHoraire`),
  ADD KEY `idAuto` (`idAuto`);

--
-- Index pour la table `horaire_trajet`
--
ALTER TABLE `horaire_trajet`
  ADD PRIMARY KEY (`idHoraire`);

--
-- Index pour la table `passager`
--
ALTER TABLE `passager`
  ADD PRIMARY KEY (`idP`),
  ADD KEY `id_Adres` (`id_Adres`);

--
-- Index pour la table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`idPay`);

--
-- Index pour la table `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`idRes`),
  ADD UNIQUE KEY `numero_reservation` (`numero_reservation`),
  ADD KEY `id_Agence` (`id_Agence`),
  ADD KEY `id_Passager` (`id_Passager`),
  ADD KEY `id_Trajet` (`id_Trajet`),
  ADD KEY `id_Payment` (`id_Payment`);

--
-- Index pour la table `trajet`
--
ALTER TABLE `trajet`
  ADD PRIMARY KEY (`id_Traj`),
  ADD KEY `id_Horaire` (`id_Horaire`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`idUt`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idAdres` (`idAdres`),
  ADD KEY `idAgenc` (`idAgenc`);

--
-- Index pour la table `ville`
--
ALTER TABLE `ville`
  ADD PRIMARY KEY (`idVil`),
  ADD KEY `id_adres` (`id_adres`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `adresse`
--
ALTER TABLE `adresse`
  MODIFY `idAdr` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `agence`
--
ALTER TABLE `agence`
  MODIFY `idAg` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `automobile`
--
ALTER TABLE `automobile`
  MODIFY `id_aut` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `courrier`
--
ALTER TABLE `courrier`
  MODIFY `idCourrier` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `depart`
--
ALTER TABLE `depart`
  MODIFY `idDep` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `horaire_trajet`
--
ALTER TABLE `horaire_trajet`
  MODIFY `idHoraire` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `passager`
--
ALTER TABLE `passager`
  MODIFY `idP` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `payment`
--
ALTER TABLE `payment`
  MODIFY `idPay` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `reservation`
--
ALTER TABLE `reservation`
  MODIFY `idRes` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `trajet`
--
ALTER TABLE `trajet`
  MODIFY `id_Traj` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `idUt` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `ville`
--
ALTER TABLE `ville`
  MODIFY `idVil` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `agence`
--
ALTER TABLE `agence`
  ADD CONSTRAINT `agence_ibfk_1` FOREIGN KEY (`id_vil`) REFERENCES `ville` (`idVil`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `automobile`
--
ALTER TABLE `automobile`
  ADD CONSTRAINT `automobile_ibfk_1` FOREIGN KEY (`id_agenc`) REFERENCES `agence` (`idAg`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `automobile_ibfk_2` FOREIGN KEY (`id_Trajet`) REFERENCES `trajet` (`id_Traj`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `courrier`
--
ALTER TABLE `courrier`
  ADD CONSTRAINT `courrier_ibfk_1` FOREIGN KEY (`agence_depart`) REFERENCES `agence` (`idAg`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `courrier_ibfk_2` FOREIGN KEY (`agence_arrive`) REFERENCES `agence` (`idAg`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `depart`
--
ALTER TABLE `depart`
  ADD CONSTRAINT `depart_ibfk_1` FOREIGN KEY (`id_Trajet`) REFERENCES `trajet` (`id_Traj`),
  ADD CONSTRAINT `depart_ibfk_2` FOREIGN KEY (`idHoraire`) REFERENCES `horaire_trajet` (`idHoraire`),
  ADD CONSTRAINT `depart_ibfk_3` FOREIGN KEY (`idAuto`) REFERENCES `automobile` (`id_aut`);

--
-- Contraintes pour la table `passager`
--
ALTER TABLE `passager`
  ADD CONSTRAINT `passager_ibfk_1` FOREIGN KEY (`id_Adres`) REFERENCES `adresse` (`idAdr`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `reservation`
--
ALTER TABLE `reservation`
  ADD CONSTRAINT `reservation_ibfk_1` FOREIGN KEY (`id_Agence`) REFERENCES `agence` (`idAg`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `reservation_ibfk_2` FOREIGN KEY (`id_Passager`) REFERENCES `passager` (`idP`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `reservation_ibfk_3` FOREIGN KEY (`id_Trajet`) REFERENCES `trajet` (`id_Traj`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `reservation_ibfk_4` FOREIGN KEY (`id_Payment`) REFERENCES `payment` (`idPay`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `trajet`
--
ALTER TABLE `trajet`
  ADD CONSTRAINT `trajet_ibfk_1` FOREIGN KEY (`id_Horaire`) REFERENCES `horaire_trajet` (`idHoraire`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD CONSTRAINT `utilisateur_ibfk_1` FOREIGN KEY (`idAdres`) REFERENCES `adresse` (`idAdr`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `utilisateur_ibfk_2` FOREIGN KEY (`idAgenc`) REFERENCES `agence` (`idAg`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `ville`
--
ALTER TABLE `ville`
  ADD CONSTRAINT `ville_ibfk_1` FOREIGN KEY (`id_adres`) REFERENCES `adresse` (`idAdr`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
