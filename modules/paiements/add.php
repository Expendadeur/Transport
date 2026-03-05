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
    $id_Reservation = $_POST['id_Reservation'] ?: null;

    if ($montant > 0 && !empty($mode_payment)) {
        try {
            $pdo->beginTransaction();
            
            // 1. Insert payment
            $stmt = $pdo->prepare("INSERT INTO payment (montant, date_payment, mode_payment, statut) VALUES (?, ?, ?, ?)");
            $stmt->execute([$montant, $date_payment, $mode_payment, $statut]);
            $payment_id = $pdo->lastInsertId();
            
            // 2. Link to reservation if provided
            if ($id_Reservation && $statut === 'validé') {
                $stmt = $pdo->prepare("UPDATE reservation SET id_Payment = ? WHERE idRes = ?");
                $stmt->execute([$payment_id, $id_Reservation]);
            }
            
            $pdo->commit();
            
            $_SESSION['success'] = "Le paiement a été enregistré" . ($id_Reservation ? " et lié à la réservation." : ".");
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Erreur: " . $e->getMessage();
        }
    } else {
        $error = "Le montant et le mode de paiement sont obligatoires.";
    }
}

// Fetch unpaid reservations for the dropdown
$unpaid_reservations = $pdo->query("
    SELECT r.idRes, p.nomP, p.prenomP, t.ville_depart, t.ville_arrive, t.prix, r.nr_place, (t.prix * r.nr_place) as total 
    FROM reservation r 
    JOIN passager p ON r.id_Passager = p.idP 
    JOIN trajet t ON r.id_Trajet = t.id_Traj 
    WHERE r.id_Payment IS NULL
    ORDER BY r.idRes DESC
")->fetchAll();

$res_id_prefill = isset($_GET['res_id']) ? (int)$_GET['res_id'] : 0;

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
            <label class="form-label">Lier à une Réservation (Optionnel)</label>
            <select name="id_Reservation" id="id_Reservation" class="form-control" onchange="updateAmountFromRes()">
                <option value="" data-amount="0">-- Paiement libre (non lié) --</option>
                <?php foreach ($unpaid_reservations as $ur): ?>
                    <option value="<?php echo $ur['idRes']; ?>" data-amount="<?php echo $ur['total']; ?>" <?php echo ($res_id_prefill == $ur['idRes']) ? 'selected' : ''; ?>>
                        #RES-<?php echo $ur['idRes']; ?> : <?php echo htmlspecialchars($ur['nomP'] . ' (' . $ur['ville_depart'] . '-' . $ur['ville_arrive'] . ')'); ?> - <?php echo number_format($ur['total'], 0); ?> FBU
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Montant (FBU) <span style="color: #ef4444;">*</span></label>
            <input type="number" name="montant" id="montant" class="form-control" placeholder="0" required>
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

<script>
function updateAmountFromRes() {
    const resSelect = document.getElementById('id_Reservation');
    const amountInput = document.getElementById('montant');
    const selected = resSelect.options[resSelect.selectedIndex];
    const amount = selected.getAttribute('data-amount');
    
    if (amount && amount > 0) {
        amountInput.value = amount;
    }
}

// Check on load for pre-filled reservation
document.addEventListener('DOMContentLoaded', updateAmountFromRes);
</script>

<?php include '../../includes/footer.php'; ?>
