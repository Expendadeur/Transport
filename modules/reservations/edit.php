<?php
/**
 * Edit Réservation
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header("Location: index.php"); exit(); }

$page_title = "Modifier la Réservation";
$error = '';

$stmt = $pdo->prepare("SELECT * FROM reservation WHERE idRes = ?");
$stmt->execute([$id]);
$r = $stmt->fetch();
if (!$r) { header("Location: index.php"); exit(); }

// Fetch Passengers, Départs, and Payments
$passagers = $pdo->query("SELECT * FROM passager ORDER BY nomP ASC")->fetchAll();
$departs = $pdo->query("
    SELECT d.idDep, d.id_Trajet, d.date_depart, d.heure_depart, d.places_disponibles, t.ville_depart, t.ville_arrive, t.prix
    FROM depart d
    JOIN trajet t ON d.id_Trajet = t.id_Traj
    WHERE d.statut = 'ouvert' OR d.idDep = " . (int)$r['id_Depart'] . "
    ORDER BY d.date_depart ASC, d.heure_depart ASC
")->fetchAll();
$payments = $pdo->query("SELECT * FROM payment WHERE statut = 'validé' ORDER BY idPay DESC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_Passager = $_POST['id_Passager'];
    $id_Depart = $_POST['id_Depart'];
    $nr_place = (int)$_POST['nr_place'];
    $id_Payment = $_POST['id_Payment'] ?: null;

    if ($id_Passager && $id_Depart) {
        try {
            $pdo->beginTransaction();

            // 1. Get info about the new voyage
            $d_stmt = $pdo->prepare("SELECT id_Trajet, date_depart, places_disponibles FROM depart WHERE idDep = ? FOR UPDATE");
            $d_stmt->execute([$id_Depart]);
            $new_trip = $d_stmt->fetch();

            if (!$new_trip) throw new Exception("Voyage introuvable.");

            // 2. Adjust seats if Voyage or Place count changed
            if ($r['id_Depart'] != $id_Depart || $r['nr_place'] != $nr_place) {
                // Restore old seats
                if ($r['id_Depart']) {
                    $pdo->prepare("UPDATE depart SET places_disponibles = places_disponibles + ? WHERE idDep = ?")
                        ->execute([$r['nr_place'], $r['id_Depart']]);
                }
                
                // Check if enough seats on new trip (re-fetch current seats after restoration if same trip)
                if ($r['id_Depart'] == $id_Depart) {
                   $current_seats = $pdo->query("SELECT places_disponibles FROM depart WHERE idDep = $id_Depart")->fetchColumn();
                } else {
                   $current_seats = $new_trip['places_disponibles'];
                }

                if ($current_seats < $nr_place) {
                    throw new Exception("Plus assez de places disponible sur ce voyage.");
                }

                // Decrement new seats
                $pdo->prepare("UPDATE depart SET places_disponibles = places_disponibles - ? WHERE idDep = ?")
                    ->execute([$nr_place, $id_Depart]);
            }

            // 3. Update Reservation
            $stmt = $pdo->prepare("UPDATE reservation SET date_reservation=?, nr_place=?, id_Passager=?, id_Trajet=?, id_Depart=?, id_Payment=? WHERE idRes=?");
            $stmt->execute([$new_trip['date_depart'], $nr_place, $id_Passager, $new_trip['id_Trajet'], $id_Depart, $id_Payment, $id]);
            
            $pdo->commit();
            $_SESSION['success'] = "Réservation mise à jour avec ajustement des places.";
            header("Location: index.php");
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Erreur: " . $e->getMessage();
        }
    } else {
        $error = "Passager et Voyage sont obligatoires.";
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="margin-bottom: 2rem;">
    <a href="index.php" style="text-decoration: none; color: var(--primary-color); font-weight: 600;">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
    <h1 style="font-size: 1.5rem; font-weight: 700; margin-top: 1rem;">Modifier Réservation #<?php echo $id; ?></h1>
</div>

<div class="card" style="max-width: 800px;">
    <form action="" method="POST">
        <div class="form-group">
            <label class="form-label">Client</label>
            <select name="id_Passager" class="form-control" required>
                <?php foreach ($passagers as $p): ?>
                    <option value="<?php echo $p['idP']; ?>" <?php echo $r['id_Passager'] == $p['idP'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($p['nomP'] . ' ' . $p['prenomP']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Voyage (Départ programmé) <span style="color: #ef4444;">*</span></label>
            <select name="id_Depart" id="id_Depart" class="form-control" required onchange="updateTripDetails()">
                <?php 
                $available = array_filter($departs, function($d) { return $d['places_disponibles'] > 0; });
                $sold_out = array_filter($departs, function($d) use ($r) { return $d['places_disponibles'] <= 0 && $d['idDep'] != $r['id_Depart']; });
                ?>
                
                <optgroup label="Actuel / Disponibles">
                    <?php foreach ($available as $d): ?>
                        <option value="<?php echo $d['idDep']; ?>" data-price="<?php echo $d['prix']; ?>" data-seats="<?php echo $d['places_disponibles']; ?>" data-trajet="<?php echo $d['id_Trajet']; ?>" <?php echo $r['id_Depart'] == $d['idDep'] ? 'selected' : ''; ?>>
                            #DEP-<?php echo $d['idDep']; ?> : <?php echo htmlspecialchars($d['ville_depart'] . ' → ' . $d['ville_arrive']); ?> 
                            (<?php echo date('d/m/Y', strtotime($d['date_depart'])); ?> à <?php echo substr($d['heure_depart'], 0, 5); ?>)
                            - <?php echo $d['places_disponibles']; ?> places
                        </option>
                    <?php endforeach; ?>
                    <?php if($r['id_Depart'] && !in_array($r['id_Depart'], array_column($available, 'idDep'))): 
                        // Current trip is full, show it selected here
                        $curr = array_filter($departs, function($d) use ($r) { return $d['idDep'] == $r['id_Depart']; });
                        $d = reset($curr);
                    ?>
                        <option value="<?php echo $d['idDep']; ?>" data-price="<?php echo $d['prix']; ?>" data-seats="0" data-trajet="<?php echo $d['id_Trajet']; ?>" selected style="color: #ef4444;">
                            [COMPLET - Actuel] #DEP-<?php echo $d['idDep']; ?> : <?php echo htmlspecialchars($d['ville_depart'] . ' → ' . $d['ville_arrive']); ?>
                        </option>
                    <?php endif; ?>
                </optgroup>

                <?php if(!empty($sold_out)): ?>
                    <optgroup label="Autres Voyages COMPLET">
                        <?php foreach ($sold_out as $d): ?>
                            <option value="<?php echo $d['idDep']; ?>" data-price="<?php echo $d['prix']; ?>" data-seats="0" data-trajet="<?php echo $d['id_Trajet']; ?>" style="color: #ef4444;">
                                [COMPLET] #DEP-<?php echo $d['idDep']; ?> : <?php echo htmlspecialchars($d['ville_depart'] . ' → ' . $d['ville_arrive']); ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endif; ?>
            </select>
            <div id="price-display" style="margin-top: 0.5rem; display: none;">
                <span style="font-weight: 700; color: var(--success-color); font-size: 1.1rem;">
                    Prix: <span id="trajet-price">0</span> FBU
                </span>
                <div id="full-trip-alert" style="display: none; color: #ef4444; margin-top: 0.5rem; font-weight: 700;">
                    <i class="fas fa-ban"></i> Ce voyage est COMPLET. Proclamez une alternative :
                    <div id="staff-next-trip" style="color: #0369a1; font-size: 0.9rem; margin-top: 0.3rem;"></div>
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Numéro de Siège</label>
                <input type="number" name="nr_place" class="form-control" value="<?php echo $r['nr_place']; ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Paiement (Filtré par prix)</label>
                <select name="id_Payment" id="id_Payment" class="form-control">
                    <option value="" data-amount="0">-- Non payé --</option>
                    <?php foreach ($payments as $pay): ?>
                        <option value="<?php echo $pay['idPay']; ?>" data-amount="<?php echo $pay['montant']; ?>" <?php echo $r['id_Payment'] == $pay['idPay'] ? 'selected' : ''; ?>>
                            #PAY-<?php echo $pay['idPay']; ?> (<?php echo number_format($pay['montant'], 0); ?> FBU)
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!$r['id_Payment']): ?>
                    <div style="margin-top: 0.5rem;">
                        <a href="../paiements/add.php?res_id=<?php echo $id; ?>" style="font-size: 0.8rem; color: #059669; font-weight: 600;">
                            <i class="fas fa-credit-card"></i> Enregistrer le paiement maintenant
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
            <a href="index.php" class="btn" style="background: #f1f5f9; color: #0f172a; text-decoration: none;">Annuler</a>
        </div>
    </form>
</div>

<script>
function updateTripDetails() {
    const departSelect = document.getElementById('id_Depart');
    const paymentSelect = document.getElementById('id_Payment');
    const priceDisplay = document.getElementById('price-display');
    const priceSpan = document.getElementById('trajet-price');
    const fullAlert = document.getElementById('full-trip-alert');
    const submitBtn = document.querySelector('button[type="submit"]');
    
    const selectedOption = departSelect.options[departSelect.selectedIndex];
    if (!selectedOption) return;
    
    const price = selectedOption.getAttribute('data-price');
    const seats = parseInt(selectedOption.getAttribute('data-seats') || 0);
    const trajetId = selectedOption.getAttribute('data-trajet');
    const departId = departSelect.value;
    const initialDepart = "<?php echo $r['id_Depart']; ?>";

    // Reset
    fullAlert.style.display = 'none';
    submitBtn.disabled = false;
    submitBtn.style.opacity = '1';
    
    if (price) {
        priceSpan.textContent = new Intl.NumberFormat().format(price);
        priceDisplay.style.display = 'block';

        if (seats <= 0 && departId !== initialDepart && departId !== "") {
            fullAlert.style.display = 'block';
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.5';
            fetchStaffNext(trajetId, departId);
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
    }
}

function fetchStaffNext(trajetId, currentId) {
    const nextDiv = document.getElementById('staff-next-trip');
    nextDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Recherche...';
    
    fetch('../../api/next_trip.php?trajet=' + trajetId + '&exclude=' + currentId)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                nextDiv.innerHTML = 'Prochain disponible: <strong>' + data.trip.date + ' à ' + data.trip.heure + '</strong> (' + data.trip.seats + ' places)';
            } else {
                nextDiv.innerHTML = 'Aucune autre disponibilité pour ce trajet.';
            }
        });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', updateTripDetails);
</script>

<?php include '../../includes/footer.php'; ?>
