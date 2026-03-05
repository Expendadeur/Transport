<?php
/**
 * Modifier un Départ
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$page_title = "Modifier Voyage #DEP-" . $id;
$error = '';

$stmt = $pdo->prepare("SELECT * FROM depart WHERE idDep = ?");
$stmt->execute([$id]);
$d = $stmt->fetch();

if (!$d) {
    header("Location: index.php");
    exit();
}

$trajets = $pdo->query("SELECT * FROM trajet ORDER BY ville_depart ASC")->fetchAll();
$vehicules = $pdo->query("SELECT * FROM automobile WHERE etat = 'en service' ORDER BY marque ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_Trajet = $_POST['id_Trajet'];
    $idAuto = $_POST['idAuto'];
    $date_depart = $_POST['date_depart'];
    $heure_depart = $_POST['heure_depart'];
    $heure_arrivee = $_POST['heure_arrivee'];
    $statut = $_POST['statut'];
    $places = (int)$_POST['places_disponibles'];

    if ($id_Trajet && $idAuto && $date_depart && $heure_depart) {
        try {
            $stmt = $pdo->prepare("UPDATE depart SET id_Trajet = ?, idAuto = ?, date_depart = ?, heure_depart = ?, heure_arrivee = ?, places_disponibles = ?, statut = ? WHERE idDep = ?");
            $stmt->execute([$id_Trajet, $idAuto, $date_depart, $heure_depart, $heure_arrivee, $places, $statut, $id]);
            
            $_SESSION['success'] = "Le voyage a été mis à jour.";
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
    <h1 style="font-size: 1.5rem; font-weight: 700; margin-top: 1rem;">Modifier le Voyage #DEP-<?php echo $id; ?></h1>
</div>

<div class="card" style="max-width: 800px;">
    <form action="" method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Itinéraire <span style="color: #ef4444;">*</span></label>
                <select name="id_Trajet" class="form-control" required>
                    <?php foreach ($trajets as $t): ?>
                        <option value="<?php echo $t['id_Traj']; ?>" <?php echo $d['id_Trajet'] == $t['id_Traj'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($t['ville_depart'] . ' → ' . $t['ville_arrive']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Véhicule <span style="color: #ef4444;">*</span></label>
                <select name="idAuto" class="form-control" required>
                    <?php foreach ($vehicules as $v): ?>
                        <option value="<?php echo $v['id_aut']; ?>" <?php echo $d['idAuto'] == $v['id_aut'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($v['marque'] . ' - ' . $v['immatriculation']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Date de Départ <span style="color: #ef4444;">*</span></label>
                <input type="date" name="date_depart" class="form-control" value="<?php echo $d['date_depart']; ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Places Restantes</label>
                <input type="number" name="places_disponibles" class="form-control" value="<?php echo $d['places_disponibles']; ?>" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Heure de Départ <span style="color: #ef4444;">*</span></label>
                <input type="time" name="heure_depart" class="form-control" value="<?php echo $d['heure_depart']; ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Heure d'Arrivée</label>
                <input type="time" name="heure_arrivee" class="form-control" value="<?php echo $d['heure_arrivee']; ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Statut</label>
            <select name="statut" class="form-control">
                <option value="ouvert" <?php echo $d['statut'] == 'ouvert' ? 'selected' : ''; ?>>Ouvert</option>
                <option value="fermé" <?php echo $d['statut'] == 'fermé' ? 'selected' : ''; ?>>Fermé (Complet/Maintenance)</option>
                <option value="terminé" <?php echo $d['statut'] == 'terminé' ? 'selected' : ''; ?>>Terminé (Arrivé)</option>
            </select>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Enregistrer les Modifications</button>
            <a href="index.php" class="btn" style="background: #f1f5f9; color: #0f172a; text-decoration: none;">Annuler</a>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
