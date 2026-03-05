<?php
/**
 * Add Paiement
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();

$page_title = "Enregistrer un Paiement";
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $montant = $_POST['montant'] ?? 0;
    $date_payment = $_POST['date_payment'] ?? date('Y-m-d');
    $mode_payment = $_POST['mode_payment'] ?? '';
    $statut = $_POST['statut'] ?? 'en attente';

    if ($montant > 0 && !empty($mode_payment)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO payment (montant, date_payment, mode_payment, statut) VALUES (?, ?, ?, ?)");
            $stmt->execute([$montant, $date_payment, $mode_payment, $statut]);
            
            $_SESSION['success'] = "Le paiement a été enregistré.";
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $error = "Erreur: " . $e->getMessage();
        }
    } else {
        $error = "Le montant et le mode de paiement sont obligatoires.";
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="margin-bottom: 2rem;">
    <a href="index.php" style="text-decoration: none; color: var(--primary-color); font-weight: 600;">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
    <h1 style="font-size: 1.5rem; font-weight: 700; margin-top: 1rem;">Nouveau Paiement</h1>
</div>

<div class="card" style="max-width: 600px;">
    <?php if ($error): ?>
        <div style="background: #fee2e2; color: #ef4444; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label class="form-label">Montant (FBU) <span style="color: #ef4444;">*</span></label>
            <input type="number" name="montant" class="form-control" placeholder="0" required>
        </div>

        <div class="form-group">
            <label class="form-label">Date du Paiement</label>
            <input type="date" name="date_payment" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Mode de Paiement <span style="color: #ef4444;">*</span></label>
            <select name="mode_payment" class="form-control" required>
                <option value="Espèces">Espèces (Cash)</option>
                <option value="Lumicash">Lumicash</option>
                <option value="Ecocash">Ecocash</option>
                <option value="Virement Bancaire">Virement Bancaire</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Statut</label>
            <select name="statut" class="form-control">
                <option value="validé">Validé</option>
                <option value="en attente" selected>En attente</option>
                <option value="annulé">Annulé</option>
            </select>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="index.php" class="btn" style="background: #f1f5f9; color: #0f172a; text-decoration: none;">Annuler</a>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
