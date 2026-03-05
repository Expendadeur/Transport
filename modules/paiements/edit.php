<?php
/**
 * Edit Paiement
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header("Location: index.php"); exit(); }

$page_title = "Modifier le Paiement";
$error = '';

$stmt = $pdo->prepare("SELECT * FROM payment WHERE idPay = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { header("Location: index.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $montant = $_POST['montant'] ?? 0;
    $date_payment = $_POST['date_payment'] ?? '';
    $mode_payment = $_POST['mode_payment'] ?? '';
    $statut = $_POST['statut'] ?? '';

    if ($montant > 0 && !empty($mode_payment)) {
        try {
            $stmt = $pdo->prepare("UPDATE payment SET montant=?, date_payment=?, mode_payment=?, statut=? WHERE idPay=?");
            $stmt->execute([$montant, $date_payment, $mode_payment, $statut, $id]);
            
            $_SESSION['success'] = "Le paiement a été mis à jour.";
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $error = "Erreur: " . $e->getMessage();
        }
    } else {
        $error = "Montant et mode de paiement sont obligatoires.";
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="margin-bottom: 2rem;">
    <a href="index.php" style="text-decoration: none; color: var(--primary-color); font-weight: 600;">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
    <h1 style="font-size: 1.5rem; font-weight: 700; margin-top: 1rem;">Modifier Paiement #<?php echo $id; ?></h1>
</div>

<div class="card" style="max-width: 600px;">
    <form action="" method="POST">
        <div class="form-group">
            <label class="form-label">Montant (FBU)</label>
            <input type="number" name="montant" class="form-control" value="<?php echo $p['montant']; ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Date</label>
            <input type="date" name="date_payment" class="form-control" value="<?php echo $p['date_payment']; ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Mode</label>
            <select name="mode_payment" class="form-control">
                <option value="Espèces" <?php echo $p['mode_payment'] == 'Espèces' ? 'selected' : ''; ?>>Espèces</option>
                <option value="Lumicash" <?php echo $p['mode_payment'] == 'Lumicash' ? 'selected' : ''; ?>>Lumicash</option>
                <option value="Ecocash" <?php echo $p['mode_payment'] == 'Ecocash' ? 'selected' : ''; ?>>Ecocash</option>
                <option value="Virement" <?php echo $p['mode_payment'] == 'Virement' ? 'selected' : ''; ?>>Virement</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Statut</label>
            <select name="statut" class="form-control">
                <option value="validé" <?php echo $p['statut'] == 'validé' ? 'selected' : ''; ?>>Validé</option>
                <option value="en attente" <?php echo $p['statut'] == 'en attente' ? 'selected' : ''; ?>>En attente</option>
                <option value="annulé" <?php echo $p['statut'] == 'annulé' ? 'selected' : ''; ?>>Annulé</option>
            </select>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Mise à jour</button>
            <a href="index.php" class="btn" style="background: #f1f5f9; color: #0f172a; text-decoration: none;">Annuler</a>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
