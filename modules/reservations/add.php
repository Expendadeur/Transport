<?php
/**
 * Add Réservation
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();

$page_title = "Faire une Réservation";
$error = '';

// Fetch Passengers, Trajets, and Payments
$passagers = $pdo->query("SELECT * FROM passager p LEFT JOIN adresse a ON p.id_Adres = a.idAdr ORDER BY p.nomP ASC")->fetchAll();
$trajets = $pdo->query("SELECT * FROM trajet ORDER BY ville_depart ASC")->fetchAll();
$payments = $pdo->query("SELECT * FROM payment WHERE statut = 'validé' ORDER BY idPay DESC LIMIT 50")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_Passager = $_POST['id_Passager'];
    $id_Trajet = $_POST['id_Trajet'];
    $date_reservation = $_POST['date_reservation'] ?? date('Y-m-d');
    $nr_place = $_POST['nr_place'] ?? 1;
    $id_Payment = $_POST['id_Payment'] ?: null;

    if ($id_Passager && $id_Trajet) {
        try {
            $stmt = $pdo->prepare("INSERT INTO reservation (date_reservation, nr_place, id_Passager, id_Trajet, id_Payment) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$date_reservation, $nr_place, $id_Passager, $id_Trajet, $id_Payment]);
            
            $_SESSION['success'] = "Réservation enregistrée avec succès.";
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $error = "Erreur: " . $e->getMessage();
        }
    } else {
        $error = "Passager et Trajet sont obligatoires.";
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="margin-bottom: 2rem;">
    <a href="index.php" style="text-decoration: none; color: var(--primary-color); font-weight: 600;">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
    <h1 style="font-size: 1.5rem; font-weight: 700; margin-top: 1rem;">Nouvelle Réservation</h1>
</div>

<div class="card" style="max-width: 800px;">
    <?php if ($error): ?>
        <div style="background: #fee2e2; color: #ef4444; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label class="form-label">Client (Passager) <span style="color: #ef4444;">*</span></label>
            <select name="id_Passager" class="form-control" required>
                <option value="">-- Sélectionner un passager --</option>
                <?php foreach ($passagers as $p): ?>
                    <option value="<?php echo $p['idP']; ?>"><?php echo htmlspecialchars($p['nomP'] . ' ' . $p['prenomP']); ?></option>
                <?php endforeach; ?>
            </select>
            <div style="margin-top: 0.5rem;">
                <a href="../clients/add.php" style="font-size: 0.8rem; color: var(--primary-color);"><i class="fas fa-user-plus"></i> Nouveau passager?</a>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Trajet <span style="color: #ef4444;">*</span></label>
                <select name="id_Trajet" id="id_Trajet" class="form-control" required onchange="updatePrice()">
                    <option value="" data-price="0">-- Choisir l'itinéraire --</option>
                    <?php foreach ($trajets as $t): ?>
                        <option value="<?php echo $t['id_Traj']; ?>" data-price="<?php echo $t['prix']; ?>">
                            <?php echo htmlspecialchars($t['ville_depart'] . ' - ' . $t['ville_arrive'] . ' (' . number_format($t['prix'], 0) . ' FBU)'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div id="price-display" style="margin-top: 0.5rem; font-weight: 700; color: var(--success-color); font-size: 1.1rem; display: none;">
                    Prix du trajet: <span id="trajet-price">0</span> FBU
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Date du voyage</label>
                <input type="date" name="date_reservation" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Numéro de Siège</label>
                <input type="number" name="nr_place" class="form-control" value="1" min="1" max="60">
            </div>
            <div class="form-group">
                <label class="form-label">Référence Paiement (Validé matching prix)</label>
                <select name="id_Payment" id="id_Payment" class="form-control">
                    <option value="" data-amount="0">-- Payer plus tard --</option>
                    <?php foreach ($payments as $pay): ?>
                        <option value="<?php echo $pay['idPay']; ?>" data-amount="<?php echo $pay['montant']; ?>">
                            #PAY-<?php echo $pay['idPay']; ?> (<?php echo number_format($pay['montant'], 0); ?> FBU)
                        </option>
                    <?php endforeach; ?>
                </select>
                <div style="margin-top: 0.5rem;">
                    <a href="../paiements/add.php" style="font-size: 0.8rem; color: var(--primary-color);"><i class="fas fa-plus-circle"></i> Enregistrer un paiement</a>
                </div>
            </div>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Valider la Réservation</button>
            <a href="index.php" class="btn" style="background: #f1f5f9; color: #0f172a; text-decoration: none;">Annuler</a>
        </div>
    </form>
</div>

<script>
function updatePrice() {
    const trajetSelect = document.getElementById('id_Trajet');
    const paymentSelect = document.getElementById('id_Payment');
    const priceDisplay = document.getElementById('price-display');
    const priceSpan = document.getElementById('trajet-price');
    
    const selectedOption = trajetSelect.options[trajetSelect.selectedIndex];
    const price = selectedOption.getAttribute('data-price');
    
    if (price && price > 0) {
        priceSpan.textContent = new Intl.NumberFormat().format(price);
        priceDisplay.style.display = 'block';
        
        // Filter payments
        Array.from(paymentSelect.options).forEach(option => {
            const amount = option.getAttribute('data-amount');
            if (amount === "0" || amount === price) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
        
        // Reset payment if current selection is hidden
        if (paymentSelect.options[paymentSelect.selectedIndex].style.display === 'none') {
            paymentSelect.value = "";
        }
    } else {
        priceDisplay.style.display = 'none';
        // Show all payments if no trajet selected
        Array.from(paymentSelect.options).forEach(option => {
            option.style.display = 'block';
        });
    }
}
</script>

<?php include '../../includes/footer.php'; ?>
