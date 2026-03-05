<?php
/**
 * Add Réservation
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();

$page_title = "Faire une Réservation";
$error = '';

// Fetch Passengers, Départs (All open ones for visibility), and Payments
$passagers = $pdo->query("SELECT * FROM passager p LEFT JOIN adresse a ON p.id_Adres = a.idAdr ORDER BY p.nomP ASC")->fetchAll();
$departs = $pdo->query("
    SELECT d.idDep, d.date_depart, d.heure_depart, d.places_disponibles, t.ville_depart, t.ville_arrive, t.prix, t.id_Traj
    FROM depart d
    JOIN trajet t ON d.id_Trajet = t.id_Traj
    WHERE d.statut = 'ouvert' AND d.date_depart >= CURDATE()
    ORDER BY d.date_depart ASC, d.heure_depart ASC
")->fetchAll();
$payments = $pdo->query("SELECT * FROM payment WHERE statut = 'validé' ORDER BY idPay DESC LIMIT 50")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_Passager = $_POST['id_Passager'];
    $id_Depart = $_POST['id_Depart'];
    $nr_place = $_POST['nr_place'] ?? 1;
    $id_Payment = $_POST['id_Payment'] ?: null;

    if ($id_Passager && $id_Depart) {
        try {
            $pdo->beginTransaction();

            // Get Depart Info
            $d_stmt = $pdo->prepare("SELECT id_Trajet, date_depart, places_disponibles FROM depart WHERE idDep = ?");
            $d_stmt->execute([$id_Depart]);
            $trip = $d_stmt->fetch();

            if ($trip && $trip['places_disponibles'] >= $nr_place) {
                // 1. Insert Reservation
                $stmt = $pdo->prepare("INSERT INTO reservation (date_reservation, nr_place, id_Passager, id_Trajet, id_Depart, id_Payment) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$trip['date_depart'], $nr_place, $id_Passager, $trip['id_Trajet'], $id_Depart, $id_Payment]);
                
                // 2. Decrement Seats
                $upd = $pdo->prepare("UPDATE depart SET places_disponibles = places_disponibles - ? WHERE idDep = ?");
                $upd->execute([$nr_place, $id_Depart]);

                $pdo->commit();
                $_SESSION['success'] = "Réservation enregistrée avec succès.";
                header("Location: index.php");
                exit();
            } else {
                throw new Exception("Places insuffisantes pour ce voyage.");
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Erreur: " . $e->getMessage();
        }
    } else {
        $error = "Passager et Voyage (Départ) sont obligatoires.";
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

        <div class="form-group">
            <label class="form-label">Sélectionner un Voyage (Départ disponible) <span style="color: #ef4444;">*</span></label>
            <select name="id_Depart" id="id_Depart" class="form-control" required onchange="updateTripDetails()">
                <option value="" data-price="0" data-seats="0" data-trajet="0">-- Choisir un voyage programmé --</option>
                
                <?php 
                $available = array_filter($departs, function($d) { return $d['places_disponibles'] > 0; });
                $sold_out = array_filter($departs, function($d) { return $d['places_disponibles'] <= 0; });
                ?>

                <?php if(!empty($available)): ?>
                    <optgroup label="Voyages Disponibles">
                        <?php foreach ($available as $d): ?>
                            <option value="<?php echo $d['idDep']; ?>" data-price="<?php echo $d['prix']; ?>" data-seats="<?php echo $d['places_disponibles']; ?>" data-trajet="<?php echo $d['id_Traj']; ?>">
                                #DEP-<?php echo $d['idDep']; ?> : <?php echo htmlspecialchars($d['ville_depart'] . ' → ' . $d['ville_arrive']); ?> 
                                (<?php echo date('d/m/Y', strtotime($d['date_depart'])); ?> à <?php echo substr($d['heure_depart'], 0, 5); ?>) 
                                - <?php echo $d['places_disponibles']; ?> places
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endif; ?>

                <?php if(!empty($sold_out)): ?>
                    <optgroup label="Voyages COMPLET (Infos départs futurs)">
                        <?php foreach ($sold_out as $d): ?>
                            <option value="<?php echo $d['idDep']; ?>" data-price="<?php echo $d['prix']; ?>" data-seats="0" data-trajet="<?php echo $d['id_Traj']; ?>" style="color: #ef4444;">
                                [COMPLET] #DEP-<?php echo $d['idDep']; ?> : <?php echo htmlspecialchars($d['ville_depart'] . ' → ' . $d['ville_arrive']); ?> 
                                (<?php echo date('d/m/Y', strtotime($d['date_depart'])); ?>)
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endif; ?>
            </select>
            <div id="trip-details" style="margin-top: 0.5rem; display: none;">
                <span style="font-weight: 700; color: var(--success-color); font-size: 1.1rem;">
                    Prix: <span id="trajet-price">0</span> FBU
                </span>
                <span id="seat-warning" style="margin-left: 1rem; color: #ef4444; font-weight: 600; font-size: 0.85rem; display: none;">
                    <i class="fas fa-exclamation-triangle"></i> Attention: Places limitées!
                </span>
                <div id="full-trip-alert" style="display: none; color: #ef4444; margin-top: 0.5rem; font-weight: 700;">
                    <i class="fas fa-ban"></i> Ce voyage est COMPLET. Veuillez choisir un autre départ.
                    <div id="staff-next-trip" style="color: #0369a1; font-size: 0.9rem; margin-top: 0.3rem;"></div>
                </div>
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
function updateTripDetails() {
    const departSelect = document.getElementById('id_Depart');
    const paymentSelect = document.getElementById('id_Payment');
    const tripDetails = document.getElementById('trip-details');
    const priceSpan = document.getElementById('trajet-price');
    const seatWarning = document.getElementById('seat-warning');
    const fullAlert = document.getElementById('full-trip-alert');
    const submitBtn = document.querySelector('button[type="submit"]');
    
    const selectedOption = departSelect.options[departSelect.selectedIndex];
    const price = selectedOption.getAttribute('data-price');
    const seats = parseInt(selectedOption.getAttribute('data-seats') || 0);
    const trajetId = selectedOption.getAttribute('data-trajet');
    const departId = departSelect.value;
    
    // Reset
    fullAlert.style.display = 'none';
    submitBtn.disabled = false;
    submitBtn.style.opacity = '1';
    
    if (price && price > 0) {
        priceSpan.textContent = new Intl.NumberFormat().format(price);
        tripDetails.style.display = 'block';
        
        if (seats <= 0 && departId !== "") {
            fullAlert.style.display = 'block';
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.5';
            fetchStaffNext(trajetId, departId);
        } else {
            seatWarning.style.display = (seats < 5) ? 'inline' : 'none';
        }
        
        // Filter payments
        Array.from(paymentSelect.options).forEach(option => {
            const amount = option.getAttribute('data-amount');
            if (amount === "0" || amount === price) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
    } else {
        tripDetails.style.display = 'none';
    }
}

function fetchStaffNext(trajetId, currentId) {
    const nextDiv = document.getElementById('staff-next-trip');
    nextDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Recherche...';
    
    fetch('../../api/next_trip.php?trajet=' + trajetId + '&exclude=' + currentId)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                nextDiv.innerHTML = 'Option alternative: <strong>' + data.trip.date + ' à ' + data.trip.heure + '</strong> (' + data.trip.seats + ' places)';
            } else {
                nextDiv.innerHTML = 'Aucun autre départ disponible pour ce trajet.';
            }
        });
}
</script>

<?php include '../../includes/footer.php'; ?>
