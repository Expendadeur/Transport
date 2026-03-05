<?php
/**
 * Public Reservation Form
 */
require_once 'config/database.php';
session_start();

$page_title = "Réservation en Ligne";

// Fetch available scheduled departures (all open ones for the next 7 days, even if full, to show info)
$departs = $pdo->query("
    SELECT d.idDep, d.id_Trajet, d.date_depart, d.heure_depart, d.places_disponibles, t.ville_depart, t.ville_arrive, t.prix, veh.capacite
    FROM depart d
    JOIN trajet t ON d.id_Trajet = t.id_Traj
    JOIN automobile veh ON d.idAuto = veh.id_aut
    WHERE d.statut = 'ouvert' AND d.date_depart >= CURDATE()
    ORDER BY d.date_depart ASC, d.heure_depart ASC
    LIMIT 20
")->fetchAll();

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
        }
        .header-simple {
            background: var(--dark-color);
            padding: 1rem 2rem;
            color: white;
            text-align: center;
        }
        .booking-container {
            max-width: 700px;
            margin: 3rem auto;
            width: 95%;
        }
        .booking-card {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 0 15px 40px rgba(0,0,0,0.08);
        }
        .trip-grid-selection {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .trip-card {
            border: 2px solid #eef2f6;
            border-radius: 16px;
            padding: 1.25rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            background: #fff;
        }
        .trip-card:hover {
            border-color: var(--primary-color);
            background: #f0f9ff;
            transform: translateY(-2px);
        }
        .trip-card.active {
            border-color: var(--primary-color);
            background: #eff6ff;
            box-shadow: 0 4px 15px rgba(3, 105, 161, 0.15);
        }
        .trip-card.disabled {
            opacity: 0.7;
            cursor: not-allowed;
            border-color: #f1f5f9;
            background: #f8fafc;
        }
        .trip-card.disabled:hover {
            transform: none;
            border-color: #f1f5f9;
        }
        .trip-info-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.8rem;
        }
        .trip-route {
            font-weight: 800;
            color: #1e293b;
            font-size: 1.1rem;
        }
        .trip-price-badge {
            background: #f0fdf4;
            color: #16a34a;
            padding: 0.4rem 0.8rem;
            border-radius: 40px;
            font-weight: 700;
            font-size: 0.85rem;
        }
        .trip-details-row {
            display: flex;
            gap: 1.5rem;
            color: #64748b;
            font-size: 0.9rem;
        }
        .trip-status-indicator {
            margin-top: 0.8rem;
            font-size: 0.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .status-available { color: #16a34a; }
        .status-warning { color: #f59e0b; }
        .status-full { color: #ef4444; }
        
        .sold-out-section {
            margin-top: 2.5rem;
            padding-top: 1.5rem;
            border-top: 1px dashed #e2e8f0;
        }
        .sold-out-title {
            font-size: 0.9rem;
            color: #94a3b8;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1rem;
        }
        #btn-next-step:disabled {
            background: #cbd5e1;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="header-simple">
        <a href="index.php" style="color: white; text-decoration: none; font-weight: 800; letter-spacing: 2px;">TRANSLOG</a>
    </div>

    <div class="booking-container">
        <div class="booking-card">
            <h2 style="text-align: center; margin-bottom: 2rem;">Réserver mon Voyage</h2>
            
            <form id="public-booking-form" action="process_public_booking.php" method="POST">
                <!-- Section 1: Voyage -->
                <div class="form-section active" id="section-1">
                    <h4 style="margin-bottom: 0.5rem; color: var(--secondary-color);">1. Où voulez-vous aller ?</h4>
                    <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 1.5rem;">Sélectionnez votre voyage parmi les départs disponibles.</p>
                    
                    <input type="hidden" name="id_Depart" id="selected_Depart" required>

                    <div class="trip-grid-selection">
                        <?php 
                        $available_trips = array_filter($departs, function($d) { return $d['places_disponibles'] > 0; });
                        $sold_out_trips = array_filter($departs, function($d) { return $d['places_disponibles'] <= 0; });

                        foreach ($available_trips as $d): 
                            $low_seats = $d['places_disponibles'] < 5;
                        ?>
                            <div class="trip-card" onclick="selectTrip(this, '<?php echo $d['idDep']; ?>', '<?php echo $d['prix']; ?>')">
                                <div class="trip-info-header">
                                    <div class="trip-route"><?php echo htmlspecialchars($d['ville_depart'] . ' → ' . $d['ville_arrive']); ?></div>
                                    <div class="trip-price-badge"><?php echo number_format($d['prix'], 0); ?> FBU</div>
                                </div>
                                <div class="trip-details-row">
                                    <span><i class="far fa-calendar-alt"></i> <?php echo date('d M Y', strtotime($d['date_depart'])); ?></span>
                                    <span><i class="far fa-clock"></i> <?php echo substr($d['heure_depart'], 0, 5); ?></span>
                                </div>
                                <div class="trip-status-indicator <?php echo $low_seats ? 'status-warning' : 'status-available'; ?>">
                                    <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                    <?php echo $d['places_disponibles']; ?> places disponibles 
                                    <?php if($low_seats): ?><span style="font-weight: 800;">- DERNIÈRES PLACES !</span><?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (!empty($sold_out_trips)): ?>
                        <div class="sold-out-section">
                            <div class="sold-out-title">Voyages Complets (Disponibilités futures)</div>
                            <?php foreach ($sold_out_trips as $d): ?>
                                <div class="trip-card disabled" style="margin-bottom: 0.5rem;">
                                    <div class="trip-info-header">
                                        <div class="trip-route" style="color: #94a3b8;"><?php echo htmlspecialchars($d['ville_depart'] . ' → ' . $d['ville_arrive']); ?></div>
                                        <div class="status-full" style="font-weight: 800; font-size: 0.75rem;">COMPLET</div>
                                    </div>
                                    <div class="trip-details-row" style="color: #cbd5e1;">
                                        <span><?php echo date('d/m/Y', strtotime($d['date_depart'])); ?> à <?php echo substr($d['heure_depart'], 0, 5); ?></span>
                                    </div>
                                    <div id="next-avail-<?php echo $d['idDep']; ?>" class="status-available" style="margin-top: 0.5rem; font-size: 0.8rem; font-weight: 700;">
                                        <i class="fas fa-spinner fa-spin"></i> Recherche du prochain...
                                    </div>
                                    <script>
                                        // Auto-fetch next avail for display only
                                        fetch('api/next_trip.php?trajet=<?php echo $d['id_Trajet']; ?>&exclude=<?php echo $d['idDep']; ?>')
                                            .then(r => r.json())
                                            .then(data => {
                                                const el = document.getElementById('next-avail-<?php echo $d['idDep']; ?>');
                                                if(data.success) {
                                                    el.innerHTML = '<i class="fas fa-arrow-right"></i> Prochain disponible : ' + data.trip.date + ' à ' + data.trip.heure;
                                                } else {
                                                    el.innerHTML = '<i class="fas fa-info-circle"></i> Pas d\'autre départ planifié.';
                                                }
                                            });
                                    </script>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div style="margin-top: 2rem; text-align: right;">
                        <button type="button" id="btn-next-step" class="btn btn-primary" onclick="nextSection(2)" disabled>
                            Détails Passager <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Section 2: Infos Passager -->
                <div class="form-section" id="section-2">
                    <h4 style="margin-bottom: 1.5rem; color: var(--secondary-color);">2. Mes Informations</h4>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Nom <span style="color: red;">*</span></label>
                            <input type="text" name="nom" class="form-control" placeholder="NDAYISHIMIYE" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Prénom <span style="color: red;">*</span></label>
                            <input type="text" name="prenom" class="form-control" placeholder="Jean" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Téléphone (WhatsApp) <span style="color: red;">*</span></label>
                        <input type="text" name="telephone" class="form-control" placeholder="68 123 456" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nombre de places <span style="font-size: 0.8rem; color: #64748b;">(Max 5)</span></label>
                        <input type="number" name="nr_place" class="form-control" value="1" min="1" max="5">
                    </div>

                    <div style="margin-top: 2rem; display: flex; justify-content: space-between;">
                        <button type="button" class="btn" style="background: #e2e8f0;" onclick="nextSection(1)"><i class="fas fa-chevron-left"></i> Retour</button>
                        <button type="submit" class="btn btn-primary" style="background: var(--success-color);">Confirmer et Imprimer mon Ticket <i class="fas fa-check"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function selectTrip(card, id, price) {
            // UI Update
            document.querySelectorAll('.trip-card').forEach(c => c.classList.remove('active'));
            card.classList.add('active');
            
            // Set Hidden Input
            document.getElementById('selected_Depart').value = id;
            
            // Enable Button
            document.getElementById('btn-next-step').disabled = false;
        }

        function nextSection(num) {
            document.querySelectorAll('.form-section').forEach(s => s.classList.remove('active'));
            document.getElementById('section-' + num).classList.add('active');
        }

        // Initialize auto-selection if trip ID is in URL
        window.onload = function() {
            const preTrip = "<?php echo $pre_selected_trip; ?>";
            if(preTrip > 0) {
                const card = document.querySelector(`.trip-card[onclick*="'${preTrip}'"]`);
                if(card) {
                    card.click();
                    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        };
    </script>
</body>
</html>
