<?php
/**
 * Public Reservation Form
 */
require_once 'config/database.php';
session_start();

$page_title = "Réservation en Ligne";
$trajets = $pdo->query("SELECT * FROM trajet ORDER BY ville_depart ASC")->fetchAll();
$pre_selected_trajet = isset($_GET['trajet']) ? (int)$_GET['trajet'] : 0;
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
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2.5rem;
            gap: 1rem;
        }
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #64748b;
        }
        .step.active {
            background: var(--primary-color);
            color: white;
        }
        .form-section {
            display: none;
        }
        .form-section.active {
            display: block;
        }
        .price-tag {
            background: #f0fdf4;
            color: #16a34a;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 800;
            margin: 1.5rem 0;
            display: none;
        }
    </style>
</head>
<body>
    <div class="header-simple">
        <a href="index.php" style="color: white; text-decoration: none; font-weight: 800; letter-spacing: 2px;">TRANSLOG</a>
    </div>

    <div class="booking-container">
        <div class="booking-card">
            <h2 style="text-align: center; margin-bottom: 2rem;">Ma Réservation à Distance</h2>
            
            <form id="public-booking-form" action="process_public_booking.php" method="POST">
                <!-- Section 1: Trajet & Date -->
                <div class="form-section active" id="section-1">
                    <h4 style="margin-bottom: 1.5rem; color: var(--secondary-color);">1. Détails du Voyage</h4>
                    <div class="form-group">
                        <label class="form-label">Destination souhaitée <span style="color: red;">*</span></label>
                        <select name="id_Trajet" id="id_Trajet" class="form-control" required onchange="updatePublicPrice()">
                            <option value="" data-price="0">-- Choisir un itinéraire --</option>
                            <?php foreach ($trajets as $t): ?>
                                <option value="<?php echo $t['id_Traj']; ?>" data-price="<?php echo $t['prix']; ?>" <?php echo ($pre_selected_trajet == $t['id_Traj']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($t['ville_depart'] . ' → ' . $t['ville_arrive']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="public-price-display" class="price-tag">
                        Prix: <span id="public-trajet-price">0</span> FBU
                    </div>

                    <div class="form-group">
                        <label class="form-label">Date du voyage <span style="color: red;">*</span></label>
                        <input type="date" name="date_reservation" class="form-control" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div style="margin-top: 2rem; text-align: right;">
                        <button type="button" class="btn btn-primary" onclick="nextSection(2)">Continuer <i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>

                <!-- Section 2: Infos Passager -->
                <div class="form-section" id="section-2">
                    <h4 style="margin-bottom: 1.5rem; color: var(--secondary-color);">2. Mes Informations</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Nom <span style="color: red;">*</span></label>
                            <input type="text" name="nom" class="form-control" placeholder="Ex: NDAYISHIMIYE" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Prénom <span style="color: red;">*</span></label>
                            <input type="text" name="prenom" class="form-control" placeholder="Jean" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Téléphone (WhatsApp de préférence) <span style="color: red;">*</span></label>
                        <input type="text" name="telephone" class="form-control" placeholder="Ex: 68 123 456" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nombre de places</label>
                        <input type="number" name="nr_place" class="form-control" value="1" min="1" max="5">
                    </div>

                    <div style="margin-top: 2rem; display: flex; justify-content: space-between;">
                        <button type="button" class="btn" style="background: #e2e8f0;" onclick="nextSection(1)"><i class="fas fa-chevron-left"></i> Retour</button>
                        <button type="submit" class="btn btn-primary" style="background: var(--success-color);">Confirmer ma Réservation <i class="fas fa-check"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function nextSection(num) {
            document.querySelectorAll('.form-section').forEach(s => s.classList.remove('active'));
            document.getElementById('section-' + num).classList.add('active');
        }

        function updatePublicPrice() {
            const select = document.getElementById('id_Trajet');
            const display = document.getElementById('public-price-display');
            const priceSpan = document.getElementById('public-trajet-price');
            
            const selected = select.options[select.selectedIndex];
            const price = selected.getAttribute('data-price');
            
            if (price && price > 0) {
                priceSpan.textContent = new Intl.NumberFormat().format(price);
                display.style.display = 'block';
            } else {
                display.style.display = 'none';
            }
        }

        // Initialize display if trajet is pre-selected
        window.onload = updatePublicPrice;
    </script>
</body>
</html>
