<?php
/**
 * Edit Trajet
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header("Location: index.php"); exit(); }

$page_title = "Modifier le Trajet";
$error = '';

// Fetch current trajet
$stmt = $pdo->prepare("SELECT * FROM trajet WHERE id_Traj = ?");
$stmt->execute([$id]);
$t = $stmt->fetch();
if (!$t) { header("Location: index.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ville_depart = $_POST['ville_depart'] ?? '';
    $ville_arrive = $_POST['ville_arrive'] ?? '';
    $distance = $_POST['distance'] ?? 0;
    $prix = $_POST['prix'] ?? 0;

    if (!empty($ville_depart) && !empty($ville_arrive) && $prix > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE trajet SET ville_depart=?, ville_arrive=?, distance=?, prix=? WHERE id_Traj=?");
            $stmt->execute([$ville_depart, $ville_arrive, $distance, $prix, $id]);
            
            $_SESSION['success'] = "Le trajet a été mis à jour.";
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $error = "Erreur: " . $e->getMessage();
        }
    } else {
        $error = "Villes et prix sont obligatoires.";
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="margin-bottom: 2rem;">
    <a href="index.php" style="text-decoration: none; color: var(--primary-color); font-weight: 600;">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
    <h1 style="font-size: 1.5rem; font-weight: 700; margin-top: 1rem;">Modifier Itinéraire : <?php echo htmlspecialchars($t['ville_depart'].' - '.$t['ville_arrive']); ?></h1>
</div>

<div class="card" style="max-width: 600px;">
    <form action="" method="POST">
        <div class="form-group">
            <label class="form-label">Ville de Départ</label>
            <input type="text" name="ville_depart" class="form-control" value="<?php echo htmlspecialchars($t['ville_depart']); ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Ville d'Arrivée</label>
            <input type="text" name="ville_arrive" class="form-control" value="<?php echo htmlspecialchars($t['ville_arrive']); ?>" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Distance (km)</label>
                <input type="number" step="0.1" name="distance" class="form-control" value="<?php echo $t['distance']; ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Prix du transport (FBU)</label>
                <input type="number" name="prix" class="form-control" value="<?php echo $t['prix']; ?>" required>
            </div>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
            <a href="index.php" class="btn" style="background: #f1f5f9; color: #0f172a; text-decoration: none;">Annuler</a>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
