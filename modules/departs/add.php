<?php
/**
 * Programmer un Nouveau Départ
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();

$page_title = "Programmer un Voyage";
$error = '';

$trajets = $pdo->query("SELECT * FROM trajet ORDER BY ville_depart ASC")->fetchAll();
$vehicules = $pdo->query("SELECT * FROM automobile WHERE etat = 'en service' ORDER BY marque ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_Trajet = $_POST['id_Trajet'];
    $idAuto = $_POST['idAuto'];
    $date_depart = $_POST['date_depart'];
    $heure_depart = $_POST['heure_depart'];
    $heure_arrivee = $_POST['heure_arrivee'];
    $statut = $_POST['statut'] ?? 'ouvert';

    // Get vehicle capacity for initial places
    $v_stmt = $pdo->prepare("SELECT capacite FROM automobile WHERE id_aut = ?");
    $v_stmt->execute([$idAuto]);
    $capacite = $v_stmt->fetchColumn();

    if ($id_Trajet && $idAuto && $date_depart && $heure_depart) {
        try {
            $stmt = $pdo->prepare("INSERT INTO depart (id_Trajet, idAuto, date_depart, heure_depart, heure_arrivee, places_disponibles, statut) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id_Trajet, $idAuto, $date_depart, $heure_depart, $heure_arrivee, $capacite, $statut]);
            
            $_SESSION['success'] = "Le voyage a été programmé avec succès.";
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $error = "Erreur: " . $e->getMessage();
        }
    } else {
        $error = "Tous les champs marqués d'une étoile sont obligatoires.";
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="margin-bottom: 2rem;">
    <a href="index.php" style="text-decoration: none; color: var(--primary-color); font-weight: 600;">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
    <h1 style="font-size: 1.5rem; font-weight: 700; margin-top: 1rem;">Programmer un Nouveau Voyage</h1>
</div>

<div class="card" style="max-width: 800px;">
    <?php if ($error): ?>
        <div style="background: #fee2e2; color: #ef4444; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Itinéraire (Trajet) <span style="color: #ef4444;">*</span></label>
                <select name="id_Trajet" class="form-control" required>
                    <option value="">-- Sélectionner un trajet --</option>
                    <?php foreach ($trajets as $t): ?>
                        <option value="<?php echo $t['id_Traj']; ?>">
                            <?php echo htmlspecialchars($t['ville_depart'] . ' → ' . $t['ville_arrive']); ?> (<?php echo number_format($t['prix'], 0); ?> FBU)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Véhicule Assigné <span style="color: #ef4444;">*</span></label>
                <select name="idAuto" class="form-control" required>
                    <option value="">-- Sélectionner un véhicule --</option>
                    <?php foreach ($vehicules as $v): ?>
                        <option value="<?php echo $v['id_aut']; ?>">
                            <?php echo htmlspecialchars($v['marque'] . ' - ' . $v['immatriculation']); ?> (<?php echo $v['capacite']; ?> places)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Date de Départ <span style="color: #ef4444;">*</span></label>
            <input type="date" name="date_depart" class="form-control" value="<?php echo date('Y-m-d'); ?>" required min="<?php echo date('Y-m-d'); ?>">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Heure de Départ <span style="color: #ef4444;">*</span></label>
                <input type="time" name="heure_depart" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Heure d'Arrivée (Estimée)</label>
                <input type="time" name="heure_arrivee" class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Statut Initial</label>
            <select name="statut" class="form-control">
                <option value="ouvert" selected>Ouvert aux réservations</option>
                <option value="fermé">Fermé (Complet ou Maintenance)</option>
            </select>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Enregistrer le Départ</button>
            <a href="index.php" class="btn" style="background: #f1f5f9; color: #0f172a; text-decoration: none;">Annuler</a>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
