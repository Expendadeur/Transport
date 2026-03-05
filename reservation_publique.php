<?php
/**
 * Public Reservation Form
 */
require_once 'config/database.php';
session_start();

$page_title = "Réservation en Ligne";

// 1. Fetch ALL agencies to ensure they all get a column
$all_agencies = $pdo->query("SELECT idAg, nom_agence FROM agence ORDER BY nom_agence ASC")->fetchAll();

// 2. Fetch all departures
$departs = $pdo->query("
    SELECT d.idDep, d.id_Trajet, d.date_depart, d.heure_depart, d.places_disponibles, 
           t.ville_depart, t.ville_arrive, t.prix, 
           veh.capacite, veh.marque, veh.modele, veh.photo,
           ag.idAg, ag.nom_agence
    FROM depart d
    JOIN trajet t ON d.id_Trajet = t.id_Traj
    JOIN automobile veh ON d.idAuto = veh.id_aut
    JOIN agence ag ON veh.id_agenc = ag.idAg
    WHERE d.statut = 'ouvert' AND d.date_depart >= CURDATE()
    ORDER BY d.date_depart ASC, d.heure_depart ASC
")->fetchAll();

// 3. Group departures by agency ID
$grouped_departs = [];
foreach ($departs as $d) {
    $grouped_departs[$d['idAg']][] = $d;
}

$pre_selected_trip = isset($_GET['trip']) ? (int)$_GET['trip'] : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réserver mon Billet - TRANSLOG</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f1f5f9;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: 'Inter', sans-serif;
        }
        .header-simple {
            background: #0f172a;
            padding: 1rem 2rem;
            color: white;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .booking-container {
            max-width: 1300px;
            margin: 2rem auto;
            width: 95%;
        }
        .dashboard-layout {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 2rem;
            align-items: start;
        }
        .agencies-scroll-container {
            display: flex;
            gap: 1.5rem;
            overflow-x: auto;
            padding-bottom: 1rem;
            scrollbar-width: thin;
        }
        .agency-column {
            min-width: 450px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        @media (max-width: 1024px) {
            .dashboard-layout { grid-template-columns: 1fr; }
            .agencies-scroll-container { flex-direction: column; overflow-x: visible; }
            .agency-column { min-width: 100%; }
        }
        .white-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            height: 100%;
        }
        .trip-grid {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .trip-card {
            border: 2px solid #f1f5f9;
            border-radius: 16px;
            padding: 1.25rem;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #fff;
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        .trip-card:hover {
            border-color: #3b82f6;
            background: #f0f9ff;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.1);
        }
        .trip-card.active {
            border-color: #3b82f6;
            background: #eff6ff;
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.2);
        }
        .trip-card.disabled {
            opacity: 0.6;
            cursor: not-allowed;
            grayscale: 1;
        }
        .trip-vehicle-thumb {
            width: 70px;
            height: 70px;
            border-radius: 10px;
            overflow: hidden;
            background: #f8fafc;
            flex-shrink: 0;
            border: 1px solid #eef2f6;
        }
        .trip-vehicle-thumb img { width: 100%; height: 100%; object-fit: cover; }
        
        .trip-main-info { flex-grow: 1; }
        .trip-route { font-weight: 800; color: #1e293b; font-size: 1rem; margin-bottom: 0.3rem; }
        .trip-meta { font-size: 0.8rem; color: #64748b; display: flex; flex-wrap: wrap; gap: 0.8rem; }
        .price-tag { font-weight: 800; color: #16a34a; font-size: 0.9rem; margin-top: 0.5rem; }
        
        .status-badge {
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            margin-top: 0.5rem;
            display: inline-block;
        }
        .badge-avail { background: #dcfce7; color: #166534; }
        .badge-warn { background: #fef3c7; color: #92400e; }
        .badge-full { background: #fee2e2; color: #991b1b; }

        .booking-form-col {
            position: sticky;
            top: 2rem;
        }
        .form-group { margin-bottom: 1.25rem; }
        .form-label { display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 0.5rem; }
        .form-control { width: 100%; padding: 0.75rem 1rem; border: 2px solid #e2e8f0; border-radius: 10px; font-family: inherit; font-size: 0.95rem; }
        .form-control:focus { outline: none; border-color: #3b82f6; background: #fff; }
        
        .summary-box {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border: 1px solid #e2e8f0;
            display: none;
        }
        .summary-item { display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="header-simple">
        <a href="index.php" style="color: white; text-decoration: none; font-weight: 800; letter-spacing: 2px; font-size: 1.5rem;">TRANSLOG</a>
    </div>

    <div class="booking-container">
        <form id="public-booking-form" action="process_public_booking.php" method="POST">
            <input type="hidden" name="id_Depart" id="selected_Depart" required>
            
            <div class="dashboard-layout">
                <div class="agencies-scroll-container">
                    <?php foreach ($all_agencies as $ag): 
                        $agency_id = $ag['idAg'];
                        $agency_trips = isset($grouped_departs[$agency_id]) ? $grouped_departs[$agency_id] : [];
                        $available = array_filter($agency_trips, function($t) { return $t['places_disponibles'] > 0; });
                        $sold_out = array_filter($agency_trips, function($t) { return $t['places_disponibles'] <= 0; });
                    ?>
                        <div class="agency-column">
                            <div class="white-card">
                                <h3 style="margin-bottom: 1.2rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #f1f5f9; padding-bottom: 0.8rem;">
                                    <span><i class="fas fa-building" style="color: #3b82f6; margin-right: 10px;"></i> <?php echo htmlspecialchars($ag['nom_agence']); ?></span>
                                    <span style="font-size: 0.7rem; color: #94a3b8; font-weight: 400;">Bureaux Ouverts</span>
                                </h3>

                                <?php if (empty($available) && empty($sold_out)): ?>
                                    <div style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                                        <i class="fas fa-calendar-times fa-3x" style="margin-bottom: 1rem; opacity: 0.3;"></i>
                                        <p style="font-weight: 600;">Aucun départ prévu</p>
                                        <p style="font-size: 0.8rem;">Revenez plus tard pour cette agence.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="trip-grid">
                                        <?php if (!empty($available)): ?>
                                            <div style="font-size: 0.8rem; font-weight: 700; color: #64748b; margin-bottom: 0.5rem; text-transform: uppercase;">Départs Disponibles</div>
                                            <?php foreach ($available as $d): 
                                                $is_warn = $d['places_disponibles'] < 5;
                                            ?>
                                                <div class="trip-card" onclick="selectTrip(this, <?php echo htmlspecialchars(json_encode($d)); ?>)">
                                                    <div class="trip-vehicle-thumb">
                                                        <?php if ($d['photo']): ?>
                                                            <img src="<?php echo $d['photo']; ?>" alt="Bus">
                                                        <?php else: ?>
                                                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #cbd5e1;">
                                                                <i class="fas fa-bus fa-2x"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="trip-main-info">
                                                        <div class="trip-route"><?php echo htmlspecialchars($d['ville_depart'] . ' → ' . $d['ville_arrive']); ?></div>
                                                        <div class="trip-meta" style="gap: 0.5rem;">
                                                            <span><?php echo date('d M', strtotime($d['date_depart'])); ?></span>
                                                            <span style="font-weight: bold; color: #0f172a;"><?php echo substr($d['heure_depart'], 0, 5); ?></span>
                                                            <span style="color: #16a34a; font-weight: bold;"><?php echo number_format($d['prix'], 0); ?> F</span>
                                                        </div>
                                                        <div class="status-badge <?php echo $is_warn ? 'badge-warn' : 'badge-avail'; ?>" style="margin-top: 0.3rem;">
                                                            <?php echo $d['places_disponibles']; ?> places
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>

                                        <?php if (!empty($sold_out)): ?>
                                            <div style="font-size: 0.8rem; font-weight: 700; color: #94a3b8; margin-top: 1.5rem; margin-bottom: 0.5rem; text-transform: uppercase;">Plus de places</div>
                                            <?php foreach ($sold_out as $d): ?>
                                                <div class="trip-card disabled" style="padding: 0.8rem;">
                                                    <div class="trip-main-info">
                                                        <div class="trip-route" style="color: #94a3b8; font-size: 0.9rem;"><?php echo htmlspecialchars($d['ville_depart'] . ' → ' . $d['ville_arrive']); ?></div>
                                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.3rem;">
                                                            <span style="font-size: 0.75rem; color: #94a3b8;"><?php echo substr($d['heure_depart'], 0, 5); ?></span>
                                                            <div class="status-badge badge-full">COMPLET</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- PASSENGER INFO (COL 3) -->
                <div class="booking-form-col">
                    <div class="white-card">
                        <h3 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.8rem;">
                            <i class="fas fa-user-check" style="color: #3b82f6;"></i> Mes Informations
                        </h3>

                        <div id="booking-summary" class="summary-box">
                            <div style="font-weight: 800; color: #1e293b; margin-bottom: 0.8rem; font-size: 0.95rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem;">
                                Résumé du voyage
                            </div>
                            <div class="summary-item">
                                <span style="color: #64748b;">Trajet:</span>
                                <span id="sum-route" style="font-weight: 700;">-</span>
                            </div>
                            <div class="summary-item">
                                <span style="color: #64748b;">Départ:</span>
                                <span id="sum-time" style="font-weight: 700;">-</span>
                            </div>
                            <div class="summary-item">
                                <span style="color: #64748b;">Prix Unit.:</span>
                                <span id="sum-price" style="font-weight: 700; color: #16a34a;">-</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Nom Complet <span style="color: #ef4444;">*</span></label>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                                <input type="text" name="nom" class="form-control" placeholder="Nom" required>
                                <input type="text" name="prenom" class="form-control" placeholder="Prénom" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Téléphone (WhatsApp) <span style="color: #ef4444;">*</span></label>
                            <input type="text" name="telephone" class="form-control" placeholder="68 123 456" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Nombre de places</label>
                            <input type="number" name="nr_place" id="nr_place" class="form-control" value="1" min="1" max="5" onchange="updateTotal()">
                        </div>

                        <div style="margin-top: 2rem;">
                            <button type="submit" id="btn-submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1rem; background: #0f172a;" disabled>
                                CONFIRMER LA RÉSERVATION
                            </button>
                            <p style="text-align: center; font-size: 0.75rem; color: #94a3b8; margin-top: 1rem;">
                                <i class="fas fa-lock"></i> Paiement sécurisé à l'embarquement
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <script>
        let selectedTripData = null;

        function selectTrip(card, data) {
            selectedTripData = data;
            
            // UI Update
            document.querySelectorAll('.trip-card').forEach(c => c.classList.remove('active'));
            card.classList.add('active');
            
            // Set Hidden Input
            document.getElementById('selected_Depart').value = data.idDep;
            
            // Update Summary
            document.getElementById('booking-summary').style.display = 'block';
            document.getElementById('sum-route').innerText = data.ville_depart + ' → ' + data.ville_arrive;
            document.getElementById('sum-time').innerText = data.date_depart + ' à ' + data.heure_depart.substring(0,5);
            document.getElementById('sum-price').innerText = new Intl.NumberFormat().format(data.prix) + ' FBU';
            
            // Enable Button
            document.getElementById('btn-submit').disabled = false;
        }

        function updateTotal() {
            // Potential for live total calculation if needed
        }

        window.onload = function() {
            const preTrip = "<?php echo $pre_selected_trip; ?>";
            if(preTrip > 0) {
                const card = document.querySelector(`.trip-card[onclick*="idDep\\":${preTrip}"]`);
                if(card) card.click();
            }
        };
    </script>
</body>
</html>
