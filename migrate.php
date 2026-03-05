<?php
/**
 * ============================================================
 * TRANSLOG - Script de Migration Complète
 * ============================================================
 * Ouvrir une seule fois dans le navigateur :
 *   http://localhost/Gestion_agence_transport/migrate.php
 *
 * Ce script :
 *   1. Crée toutes les tables (DROP + CREATE)
 *   2. Insère toutes les données originales
 *   3. Hache correctement tous les mots de passe (bcrypt)
 *   4. Ajoute les nouvelles tables (depenses, chauffeur enrichi)
 * ============================================================
 */

// ── Connexion directe sans passer par config/database.php ────
$host     = 'localhost';
$dbname   = 'transport';
$username = 'root';
$dbpass   = '00000000';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $dbpass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    die("<b style='color:red'>Connexion échouée :</b> " . $e->getMessage());
}

$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

$steps = [];

// ── Helper ────────────────────────────────────────────────────
function run(PDO $pdo, string $sql, string $label, array &$steps): void {
    try {
        $pdo->exec($sql);
        $steps[] = ['ok', $label];
    } catch (PDOException $e) {
        $steps[] = ['err', "$label — " . $e->getMessage()];
    }
}

// ════════════════════════════════════════════════════════════
// 1. SUPPRESSION DES TABLES EXISTANTES (ordre inversé des FK)
// ════════════════════════════════════════════════════════════
$drops = [
    'depart', 'automobile', 'trajet', 'horaire_trajet', 'courrier',
    'reservation', 'payment', 'passager', 'utilisateur', 'chauffeur',
    'depenses', 'agence', 'ville', 'adresse'
];
foreach ($drops as $t) {
    run($pdo, "DROP TABLE IF EXISTS `$t`", "DROP TABLE $t", $steps);
}

// ════════════════════════════════════════════════════════════
// 2. CRÉATION DES TABLES
// ════════════════════════════════════════════════════════════

run($pdo, "
CREATE TABLE `adresse` (
  `idAdr` int NOT NULL AUTO_INCREMENT,
  `pays` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `commune` varchar(100) DEFAULT NULL,
  `zone` varchar(100) DEFAULT NULL,
  `quartier` varchar(100) DEFAULT NULL,
  `avenue` varchar(100) DEFAULT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `tel` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`idAdr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
", "CREATE TABLE adresse", $steps);

run($pdo, "
CREATE TABLE `ville` (
  `idVil` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `station` varchar(100) DEFAULT NULL,
  `id_adres` int DEFAULT NULL,
  PRIMARY KEY (`idVil`),
  KEY `id_adres` (`id_adres`),
  CONSTRAINT `ville_ibfk_1` FOREIGN KEY (`id_adres`) REFERENCES `adresse` (`idAdr`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
", "CREATE TABLE ville", $steps);

run($pdo, "
CREATE TABLE `agence` (
  `idAg` int NOT NULL AUTO_INCREMENT,
  `nom_agence` varchar(150) NOT NULL,
  `tel_agence` varchar(20) DEFAULT NULL,
  `id_vil` int DEFAULT NULL,
  PRIMARY KEY (`idAg`),
  KEY `id_vil` (`id_vil`),
  CONSTRAINT `agence_ibfk_1` FOREIGN KEY (`id_vil`) REFERENCES `ville` (`idVil`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
", "CREATE TABLE agence", $steps);

run($pdo, "
CREATE TABLE `utilisateur` (
  `idUt` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `genre` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','gestionnaire','agent','chauffeur') NOT NULL DEFAULT 'agent',
  `idAdres` int DEFAULT NULL,
  `idAgenc` int DEFAULT NULL,
  `statut` enum('actif','inactif') NOT NULL DEFAULT 'actif',
  PRIMARY KEY (`idUt`),
  UNIQUE KEY `email` (`email`),
  KEY `idAdres` (`idAdres`),
  KEY `idAgenc` (`idAgenc`),
  CONSTRAINT `utilisateur_ibfk_1` FOREIGN KEY (`idAdres`) REFERENCES `adresse` (`idAdr`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `utilisateur_ibfk_2` FOREIGN KEY (`idAgenc`) REFERENCES `agence` (`idAg`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
", "CREATE TABLE utilisateur", $steps);

run($pdo, "
CREATE TABLE `chauffeur` (
  `id_Chauff` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `num_permis` varchar(50) DEFAULT NULL,
  `categorie_permis` varchar(10) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `statut` enum('actif','inactif') DEFAULT 'actif',
  PRIMARY KEY (`id_Chauff`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
", "CREATE TABLE chauffeur", $steps);

run($pdo, "
CREATE TABLE `horaire_trajet` (
  `idHoraire` int NOT NULL AUTO_INCREMENT,
  `date_depart` date DEFAULT NULL,
  `heure_depart` time DEFAULT NULL,
  PRIMARY KEY (`idHoraire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
", "CREATE TABLE horaire_trajet", $steps);

run($pdo, "
CREATE TABLE `trajet` (
  `id_Traj` int NOT NULL AUTO_INCREMENT,
  `ville_depart` varchar(100) DEFAULT NULL,
  `ville_arrive` varchar(100) DEFAULT NULL,
  `distance` decimal(8,2) DEFAULT NULL,
  `prix` decimal(10,2) DEFAULT NULL,
  `id_Horaire` int DEFAULT NULL,
  PRIMARY KEY (`id_Traj`),
  KEY `id_Horaire` (`id_Horaire`),
  CONSTRAINT `trajet_ibfk_1` FOREIGN KEY (`id_Horaire`) REFERENCES `horaire_trajet` (`idHoraire`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
", "CREATE TABLE trajet", $steps);

run($pdo, "
CREATE TABLE `automobile` (
  `id_aut` int NOT NULL AUTO_INCREMENT,
  `marque` varchar(100) DEFAULT NULL,
  `modele` varchar(100) DEFAULT NULL,
  `immatriculation` varchar(50) DEFAULT NULL,
  `capacite` int DEFAULT NULL,
  `etat` enum('en service','en panne','en maintenance') DEFAULT 'en service',
  `id_agenc` int DEFAULT NULL,
  `id_Trajet` int DEFAULT NULL,
  PRIMARY KEY (`id_aut`),
  KEY `id_agenc` (`id_agenc`),
  KEY `id_Trajet` (`id_Trajet`),
  CONSTRAINT `automobile_ibfk_1` FOREIGN KEY (`id_agenc`) REFERENCES `agence` (`idAg`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `automobile_ibfk_2` FOREIGN KEY (`id_Trajet`) REFERENCES `trajet` (`id_Traj`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
", "CREATE TABLE automobile", $steps);

run($pdo, "
CREATE TABLE `passager` (
  `idP` int NOT NULL AUTO_INCREMENT,
  `nomP` varchar(100) DEFAULT NULL,
  `prenomP` varchar(100) DEFAULT NULL,
  `genre` varchar(20) DEFAULT NULL,
  `age` int DEFAULT NULL,
  `telephone` int NOT NULL,
  `id_Adres` int DEFAULT NULL,
  PRIMARY KEY (`idP`),
  KEY `id_Adres` (`id_Adres`),
  CONSTRAINT `passager_ibfk_1` FOREIGN KEY (`id_Adres`) REFERENCES `adresse` (`idAdr`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
", "CREATE TABLE passager", $steps);

run($pdo, "
CREATE TABLE `payment` (
  `idPay` int NOT NULL AUTO_INCREMENT,
  `montant` decimal(10,2) DEFAULT NULL,
  `date_payment` date DEFAULT NULL,
  `mode_payment` varchar(50) DEFAULT NULL,
  `statut` enum('validé','en attente','annulé') DEFAULT 'en attente',
  PRIMARY KEY (`idPay`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
", "CREATE TABLE payment", $steps);

run($pdo, "
CREATE TABLE `reservation` (
  `idRes` int NOT NULL AUTO_INCREMENT,
  `date_reservation` date DEFAULT NULL,
  `nr_place` int DEFAULT NULL,
  `statut` enum('validee','annulee') DEFAULT 'validee',
  `id_Agence` int DEFAULT NULL,
  `id_Passager` int DEFAULT NULL,
  `id_Trajet` int DEFAULT NULL,
  `id_Payment` int DEFAULT NULL,
  `numero_reservation` varchar(20) DEFAULT NULL,
  `date_annulation` datetime DEFAULT NULL,
  `motif_annulation` text,
  `prix_total` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`idRes`),
  UNIQUE KEY `numero_reservation` (`numero_reservation`),
  KEY `id_Agence` (`id_Agence`),
  KEY `id_Passager` (`id_Passager`),
  KEY `id_Trajet` (`id_Trajet`),
  KEY `id_Payment` (`id_Payment`),
  CONSTRAINT `reservation_ibfk_1` FOREIGN KEY (`id_Agence`) REFERENCES `agence` (`idAg`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `reservation_ibfk_2` FOREIGN KEY (`id_Passager`) REFERENCES `passager` (`idP`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `reservation_ibfk_3` FOREIGN KEY (`id_Trajet`) REFERENCES `trajet` (`id_Traj`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `reservation_ibfk_4` FOREIGN KEY (`id_Payment`) REFERENCES `payment` (`idPay`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
", "CREATE TABLE reservation", $steps);

run($pdo, "
CREATE TABLE `courrier` (
  `idCourrier` int NOT NULL AUTO_INCREMENT,
  `description` text,
  `expediteur` varchar(150) DEFAULT NULL,
  `destinateur` varchar(150) DEFAULT NULL,
  `agence_depart` int DEFAULT NULL,
  `agence_arrive` int DEFAULT NULL,
  `date_expedition` datetime DEFAULT NULL,
  `date_reception` datetime DEFAULT NULL,
  `poids` decimal(8,2) DEFAULT NULL,
  `prix` decimal(10,2) DEFAULT NULL,
  `statut` enum('en transit','livré','retourné') DEFAULT 'en transit',
  `code_suivi` varchar(30) DEFAULT NULL,
  `type_courrier` varchar(50) DEFAULT NULL,
  `signature_reception` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`idCourrier`),
  UNIQUE KEY `code_suivi` (`code_suivi`),
  KEY `agence_depart` (`agence_depart`),
  KEY `agence_arrive` (`agence_arrive`),
  CONSTRAINT `courrier_ibfk_1` FOREIGN KEY (`agence_depart`) REFERENCES `agence` (`idAg`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `courrier_ibfk_2` FOREIGN KEY (`agence_arrive`) REFERENCES `agence` (`idAg`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
", "CREATE TABLE courrier", $steps);

run($pdo, "
CREATE TABLE `depenses` (
  `idDep` int NOT NULL AUTO_INCREMENT,
  `description` varchar(255) DEFAULT NULL,
  `montant` decimal(10,2) DEFAULT NULL,
  `date_depense` date DEFAULT NULL,
  `idAgenc` int DEFAULT NULL,
  PRIMARY KEY (`idDep`),
  KEY `idAgenc` (`idAgenc`),
  CONSTRAINT `depenses_ibfk_1` FOREIGN KEY (`idAgenc`) REFERENCES `agence` (`idAg`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
", "CREATE TABLE depenses", $steps);

run($pdo, "
CREATE TABLE `depart` (
  `idDep` int NOT NULL AUTO_INCREMENT,
  `id_Trajet` int DEFAULT NULL,
  `idHoraire` int DEFAULT NULL,
  `idAuto` int DEFAULT NULL,
  `date_depart` date DEFAULT NULL,
  `places_disponibles` int DEFAULT NULL,
  PRIMARY KEY (`idDep`),
  KEY `id_Trajet` (`id_Trajet`),
  KEY `idHoraire` (`idHoraire`),
  KEY `idAuto` (`idAuto`),
  CONSTRAINT `depart_ibfk_1` FOREIGN KEY (`id_Trajet`) REFERENCES `trajet` (`id_Traj`),
  CONSTRAINT `depart_ibfk_2` FOREIGN KEY (`idHoraire`) REFERENCES `horaire_trajet` (`idHoraire`),
  CONSTRAINT `depart_ibfk_3` FOREIGN KEY (`idAuto`) REFERENCES `automobile` (`id_aut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
", "CREATE TABLE depart", $steps);

// ════════════════════════════════════════════════════════════
// 3. DONNÉES — ORDRE RESPECTANT LES CLÉS ÉTRANGÈRES
// ════════════════════════════════════════════════════════════

// adresse
run($pdo, "
INSERT INTO `adresse` (`idAdr`, `pays`, `province`, `commune`, `zone`, `quartier`, `avenue`, `numero`, `tel`) VALUES
(1, 'Burundi', 'Gitega',   'Buhiga',  'Murere', 'Tenga',      'Mwakiro',  '30', '+257 79 000 001'),
(2, 'Burundi', 'Bujumbura','Burunga', 'Mpanda', 'Bukirasazi', 'Nyambuye', '16', '+257 69 000 002'),
(3, 'Burundi', 'Bujumbura','Mukaza',  'Centre', 'Centre-Ville','Avenue de l''Uprona', '5', '+257 72 000 003'),
(4, 'Burundi', 'Bujumbura','Buhiga',  'Murere', 'Musinzira',  'Gasasa',   '20', '+257 68 000 004');
", "INSERT adresse", $steps);

// ville
run($pdo, "
INSERT INTO `ville` (`idVil`, `nom`, `station`, `id_adres`) VALUES
(1, 'Bujumbura', 'Station COTEBU', NULL),
(2, 'Gitega',    'Gare Routière Gitega', NULL),
(3, 'Ngozi',     'Terminus Ngozi', NULL),
(4, 'Rumonge',   'Station Rumonge', NULL);
", "INSERT ville", $steps);

// agence
run($pdo, "
INSERT INTO `agence` (`idAg`, `nom_agence`, `tel_agence`, `id_vil`) VALUES
(1, 'TRANSLOG Bujumbura', '+257 22 22 00 01', 1),
(2, 'TRANSLOG Gitega',    '+257 22 22 00 02', 2),
(3, 'TRANSLOG Ngozi',     '+257 22 22 00 03', 3);
", "INSERT agence", $steps);

// utilisateur — mots de passe hashés bcrypt
$users = [
    [2, 'Nihorimbere', 'Désiré',    'desire@gmail.com',    'M', 'desire123',    'admin',        1, null, 'actif'],
    [3, 'Nduwayo',     'Salvator',  'salvator@gmail.com',  'M', 'salvat123',    'gestionnaire', 1, 1,    'actif'],
    [4, 'Ndikuriyo',   'Ferdinand', 'ferdinand@gmail.com', 'M', 'ferdinand345', 'agent',        2, 1,    'actif'],
    [5, 'Niyongere',   'Chantal',   'chantal@gmail.com',   'F', 'chantal45',    'gestionnaire', 2, 2,    'actif'],
    [6, 'Nduwarugira', 'Thierry',   'thierry@gmail.com',   'M', 'thierry60',    'agent',        3, 2,    'actif'],
    [8, 'Ngabireyimana','Vanessa',  'vanessa@gmail.com',   'F', 'vanbe100',     'chauffeur',    3, null,  'actif'],
];

$stmt = $pdo->prepare("INSERT INTO utilisateur (idUt, nom, prenom, email, genre, password, role, idAdres, idAgenc, statut) VALUES (?,?,?,?,?,?,?,?,?,?)");
foreach ($users as $u) {
    $hashed = password_hash($u[5], PASSWORD_DEFAULT);
    try {
        $stmt->execute([$u[0], $u[1], $u[2], $u[3], $u[4], $hashed, $u[6], $u[7], $u[8], $u[9]]);
        $steps[] = ['ok', "INSERT utilisateur — {$u[2]} {$u[1]} ({$u[6]}) mot de passe: <code>{$u[5]}</code>"];
    } catch(PDOException $e) {
        $steps[] = ['err', "INSERT utilisateur {$u[3]} — " . $e->getMessage()];
    }
}

// chauffeur (table dédiée) — avec login
$chauffeurs = [
    ['Hakizimana', 'Prosper',  '+257 79 111 001', 'A12345/BUJ', 'C', 'prosper@translog.bi',  'prosper123'],
    ['Irakoze',    'Joël',     '+257 69 222 002', 'B67890/GIT', 'D', 'joel@translog.bi',     'joel2024'],
    ['Nkunzimana', 'Étienne',  '+257 72 333 003', 'C11111/NGZ', 'B', 'etienne@translog.bi',  'etienne45'],
];

$stmt2 = $pdo->prepare("INSERT INTO chauffeur (nom, prenom, telephone, num_permis, categorie_permis, email, password, statut) VALUES (?,?,?,?,?,?,?,?)");
foreach ($chauffeurs as $c) {
    $hashed = password_hash($c[6], PASSWORD_DEFAULT);
    try {
        $stmt2->execute([$c[0], $c[1], $c[2], $c[3], $c[4], $c[5], $hashed, 'actif']);
        $steps[] = ['ok', "INSERT chauffeur — {$c[1]} {$c[0]} login: <code>{$c[5]}</code> / mdp: <code>{$c[6]}</code>"];
    } catch(PDOException $e) {
        $steps[] = ['err', "INSERT chauffeur {$c[0]} — " . $e->getMessage()];
    }
}

// horaire_trajet
run($pdo, "
INSERT INTO `horaire_trajet` (`idHoraire`, `date_depart`, `heure_depart`) VALUES
(1, CURDATE(), '06:00:00'),
(2, CURDATE(), '12:00:00');
", "INSERT horaire_trajet", $steps);

// trajet
run($pdo, "
INSERT INTO `trajet` (`id_Traj`, `ville_depart`, `ville_arrive`, `distance`, `prix`, `id_Horaire`) VALUES
(1, 'Bujumbura', 'Gitega',  65.00, 12000.00, 1),
(2, 'Bujumbura', 'Ngozi',  145.00,  9000.00, 2),
(3, 'Gitega', 'Ngozi',     88.00,  7500.00, NULL),
(4, 'Bujumbura', 'Rumonge', 72.00, 11000.00, NULL);
", "INSERT trajet", $steps);

// automobile
run($pdo, "
INSERT INTO `automobile` (`id_aut`, `marque`, `modele`, `immatriculation`, `capacite`, `etat`, `id_agenc`, `id_Trajet`) VALUES
(1, 'Toyota',    'Coaster',  'BJ 1234 A', 30, 'en service',    1, 1),
(2, 'Mitsubishi','Rosa',     'GT 5678 B', 25, 'en service',    2, 2),
(3, 'Isuzu',     'Minibus',  'BJ 9012 C', 20, 'en maintenance',1, 3);
", "INSERT automobile", $steps);

// passager
run($pdo, "
INSERT INTO `passager` (`idP`, `nomP`, `prenomP`, `genre`, `age`, `telephone`, `id_Adres`) VALUES
(1, 'Muhimpundu', 'Ange',  'Femme', 25, '0', 1),
(2, 'Karikurubu', 'Rene',  'Homme', 34, '0', 2),
(3, 'Bukuru',     'Mathieu', 'Masculin', 30, '68', 4);
", "INSERT passager", $steps);

// payment
run($pdo, "
INSERT INTO `payment` (`idPay`, `montant`, `date_payment`, `mode_payment`, `statut`) VALUES
(1, 12000.00, CURDATE(), 'espèces',      'validé'),
(2,  9000.00, CURDATE(), 'mobile money', 'validé'),
(3, 12000.00, CURDATE(), 'espèces',      'en attente');
", "INSERT payment", $steps);

// reservation
run($pdo, "
INSERT INTO `reservation` (`idRes`, `date_reservation`, `nr_place`, `id_Passager`, `id_Trajet`, `id_Payment`) VALUES
(1, CURDATE(), 3, 1, 1, 1),
(2, CURDATE(), 7, 2, 2, 2),
(3, CURDATE(), 1, 3, 1, 3);
", "INSERT reservation", $steps);

// depenses (sample)
run($pdo, "
INSERT INTO `depenses` (`idDep`, `description`, `montant`, `date_depense`, `idAgenc`) VALUES
(1, 'Carburant véhicule BJ 1234 A',  45000.00, CURDATE(), 1),
(2, 'Entretien mensuel Toyota Coaster', 80000.00, CURDATE(), 1),
(3, 'Salaires chauffeurs - Gitega',  120000.00, CURDATE(), 2);
", "INSERT depenses", $steps);

$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

// ════════════════════════════════════════════════════════════
// AFFICHAGE DU RÉSULTAT
// ════════════════════════════════════════════════════════════
$ok_count  = count(array_filter($steps, fn($s) => $s[0] === 'ok'));
$err_count = count(array_filter($steps, fn($s) => $s[0] === 'err'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Migration TRANSLOG</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; padding: 2rem; color: #0f172a; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #1e293b, #334155); color: white; border-radius: 16px; padding: 2rem; margin-bottom: 2rem; text-align: center; }
        .header h1 { font-size: 1.8rem; margin-bottom: 0.5rem; }
        .summary { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem; }
        .sum-card { border-radius: 12px; padding: 1.5rem; text-align: center; }
        .sum-ok  { background: #ecfdf5; border: 2px solid #10b981; }
        .sum-err { background: #fee2e2; border: 2px solid #ef4444; }
        .sum-card .num { font-size: 2rem; font-weight: 800; }
        .sum-ok  .num { color: #059669; }
        .sum-err .num { color: #ef4444; }
        .log { background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .log h3 { margin-bottom: 1rem; color: #64748b; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; }
        .step { display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9; font-size: 0.85rem; }
        .step:last-child { border-bottom: none; }
        .badge { width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; flex-shrink: 0; margin-top: 1px; }
        .badge-ok  { background: #10b981; color: white; }
        .badge-err { background: #ef4444; color: white; }
        .creds { background: #fef3c7; border: 2px solid #f59e0b; border-radius: 12px; padding: 1.5rem; margin-top: 2rem; }
        .creds h3 { color: #92400e; margin-bottom: 1rem; }
        table.cred-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        table.cred-table th { background: #fde68a; color: #92400e; padding: 0.5rem 0.75rem; text-align: left; }
        table.cred-table td { padding: 0.5rem 0.75rem; border-bottom: 1px solid #fde68a; }
        .warning { background: #fee2e2; border: 2px solid #ef4444; border-radius: 12px; padding: 1rem; margin-top: 1rem; color: #991b1b; font-size: 0.85rem; }
        .btn { display: inline-block; background: #2563eb; color: white; padding: 0.75rem 2rem; border-radius: 8px; text-decoration: none; font-weight: 700; margin-top: 1.5rem; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🚌 TRANSLOG – Migration de la Base de Données</h1>
        <p style="opacity: 0.8; margin-top: 0.5rem;">Base : <strong><?php echo $dbname; ?></strong> @ <?php echo $host; ?></p>
    </div>

    <div class="summary">
        <div class="sum-card sum-ok">
            <div class="num"><?php echo $ok_count; ?></div>
            <div style="color: #059669; font-weight: 600; margin-top: 0.3rem;">Opérations réussies</div>
        </div>
        <div class="sum-card sum-err">
            <div class="num"><?php echo $err_count; ?></div>
            <div style="color: #ef4444; font-weight: 600; margin-top: 0.3rem;">Erreurs</div>
        </div>
    </div>

    <div class="log">
        <h3>Journal de migration</h3>
        <?php foreach ($steps as $step): ?>
            <div class="step">
                <div class="badge badge-<?php echo $step[0]; ?>"><?php echo $step[0] === 'ok' ? '✓' : '✗'; ?></div>
                <span><?php echo $step[1]; ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Credentials Table -->
    <div class="creds">
        <h3>🔐 Identifiants de Connexion</h3>
        <table class="cred-table">
            <tr><th>Nom</th><th>Email</th><th>Mot de passe</th><th>Rôle</th></tr>
            <tr><td>Nihorimbere Désiré</td><td>desire@gmail.com</td><td>desire123</td><td>Admin</td></tr>
            <tr><td>Nduwayo Salvator</td><td>salvator@gmail.com</td><td>salvat123</td><td>Gestionnaire</td></tr>
            <tr><td>Ndikuriyo Ferdinand</td><td>ferdinand@gmail.com</td><td>ferdinand345</td><td>Agent</td></tr>
            <tr><td>Niyongere Chantal</td><td>chantal@gmail.com</td><td>chantal45</td><td>Gestionnaire</td></tr>
            <tr><td>Nduwarugira Thierry</td><td>thierry@gmail.com</td><td>thierry60</td><td>Agent</td></tr>
            <tr><td>Ngabireyimana Vanessa</td><td>vanessa@gmail.com</td><td>vanbe100</td><td>Chauffeur (via employe)</td></tr>
            <tr style="background: #fef9c3;"><td>Hakizimana Prosper</td><td>prosper@translog.bi</td><td>prosper123</td><td>🚗 Chauffeur</td></tr>
            <tr style="background: #fef9c3;"><td>Irakoze Joël</td><td>joel@translog.bi</td><td>joel2024</td><td>🚗 Chauffeur</td></tr>
            <tr style="background: #fef9c3;"><td>Nkunzimana Étienne</td><td>etienne@translog.bi</td><td>etienne45</td><td>🚗 Chauffeur</td></tr>
        </table>

        <div class="warning">
            ⚠️ <strong>Sécurité :</strong> Changez tous ces mots de passe après la première connexion. 
            Supprimez ce fichier <code>migrate.php</code> du serveur une fois la migration effectuée.
        </div>
    </div>

    <?php if ($err_count === 0): ?>
        <div style="text-align: center;">
            <a href="/Gestion_agence_transport/login.php" class="btn">✅ Migration réussie — Aller à la connexion</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
