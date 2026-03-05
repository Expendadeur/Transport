<?php
/**
 * Add Trajet
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();

$page_title = "Ajouter un Trajet";
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ville_depart = $_POST['ville_depart'] ?? '';
    $ville_arrive = $_POST['ville_arrive'] ?? '';
    $distance = $_POST['distance'] ?? 0;
    $prix = $_POST['prix'] ?? 0;

    if (!empty($ville_depart) && !empty($ville_arrive) && $prix > 0) {
        try {
            $stmt = $pdo->prepare("INSERT INTO trajet (ville_depart, ville_arrive, distance, prix) VALUES (?, ?, ?, ?)");
            $stmt->execute([$ville_depart, $ville_arrive, $distance, $prix]);
            
            $_SESSION['success'] = "Le trajet a été créé avec succès.";
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $error = "Erreur lors de la création: " . $e->getMessage();
        }
    } else {
        $error = "Villes de départ/arrivée et prix sont obligatoires.";
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="margin-bottom: 2rem;">
    <a href="index.php" style="text-decoration: none; color: var(--primary-color); font-weight: 600;">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
    <h1 style="font-size: 1.5rem; font-weight: 700; margin-top: 1rem;">Nouveau Itinéraire</h1>
</div>

<div class="card" style="max-width: 600px;">
    <?php if ($error): ?>
        <div style="background: #fee2e2; color: #ef4444; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label class="form-label">Ville de Départ <span style="color: #ef4444;">*</span></label>
            <input type="text" name="ville_depart" class="form-control" placeholder="Ex: Bujumbura" required>
        </div>

        <div class="form-group">
            <label class="form-label">Ville d'Arrivée <span style="color: #ef4444;">*</span></label>
            <input type="text" name="ville_arrive" class="form-control" placeholder="Ex: Gitega" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Distance (km)</label>
                <input type="number" step="0.1" name="distance" class="form-control" placeholder="Ex: 65.5">
            </div>
            <div class="form-group">
                <label class="form-label">Prix du transport (FBU) <span style="color: #ef4444;">*</span></label>
                <input type="number" name="prix" class="form-control" placeholder="Ex: 12000" required>
            </div>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Créer le Trajet</button>
            <a href="index.php" class="btn" style="background: #f1f5f9; color: #0f172a; text-decoration: none;">Annuler</a>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
