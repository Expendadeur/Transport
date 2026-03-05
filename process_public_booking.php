<?php
/**
 * Public Booking Processor
 */
require_once 'config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $telephone = trim($_POST['telephone']);
    $id_Depart = (int)$_POST['id_Depart'];
    $nr_place = (int)$_POST['nr_place'];

    if (!$nom || !$telephone || !$id_Depart) {
        die("Erreur: Données incomplètes.");
    }

    try {
        $pdo->beginTransaction();

        // 1. Get Depart info and capacity
        $d_stmt = $pdo->prepare("SELECT id_Trajet, date_depart, places_disponibles FROM depart WHERE idDep = ? FOR UPDATE");
        $d_stmt->execute([$id_Depart]);
        $trip = $d_stmt->fetch();

        if (!$trip || $trip['places_disponibles'] < $nr_place) {
            throw new Exception("Places insuffisantes ou voyage non trouvé.");
        }

        // 2. Check/Create Passenger
        $stmt = $pdo->prepare("SELECT idP FROM passager WHERE telephone = ? LIMIT 1");
        $stmt->execute([$telephone]);
        $passager = $stmt->fetch();

        if ($passager) {
            $id_Passager = $passager['idP'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO passager (nomP, prenomP, telephone) VALUES (?, ?, ?)");
            $stmt->execute([$nom, $prenom, $telephone]);
            $id_Passager = $pdo->lastInsertId();
        }

        // 3. Insert reservation
        $stmt = $pdo->prepare("INSERT INTO reservation (date_reservation, nr_place, id_Passager, id_Trajet, id_Depart, id_Payment) VALUES (?, ?, ?, ?, ?, NULL)");
        $stmt->execute([$trip['date_depart'], $nr_place, $id_Passager, $trip['id_Trajet'], $id_Depart]);
        $resId = $pdo->lastInsertId();

        // 4. Decrement seats
        $upd = $pdo->prepare("UPDATE depart SET places_disponibles = places_disponibles - ? WHERE idDep = ?");
        $upd->execute([$nr_place, $id_Depart]);

        $pdo->commit();

        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <title>Succès - TRANSLOG</title>
            <link rel="stylesheet" href="assets/css/style.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <style>
                body { background: #f1f5f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 2rem; }
                .success-card { background: white; padding: 3rem; border-radius: 20px; text-align: center; box-shadow: var(--shadow); max-width: 550px; width: 100%; }
                .ticket-btn { display: inline-block; background: #0369a1; color: white; padding: 1rem 2rem; border-radius: 12px; text-decoration: none; font-weight: 700; margin-top: 2rem; box-shadow: 0 4px 15px rgba(3, 105, 161, 0.3); transition: transform 0.2s; }
                .ticket-btn:hover { transform: translateY(-2px); background: #0284c7; }
            </style>
        </head>
        <body>
            <div class="success-card">
                <i class="fas fa-check-circle" style="font-size: 5rem; color: #16a34a; margin-bottom: 2rem;"></i>
                <h2 style="margin-bottom: 1rem;">Réservation Confirmée !</h2>
                <p style="color: #64748b; margin-bottom: 2rem;">Votre réservation <strong>#RES-<?php echo $resId; ?></strong> est maintenant enregistrée. Vous pouvez télécharger votre billet ci-dessous.</p>
                
                <a href="modules/reservations/ticket.php?id=<?php echo $resId; ?>&public=1" class="ticket-btn">
                    <i class="fas fa-file-pdf"></i> TELECHARGER MON BILLET
                </a>
                
                <div style="margin-top: 1.5rem;">
                    <a href="index.php" style="color: #64748b; font-size: 0.9rem; text-decoration: none;">Retour à l'accueil</a>
                </div>
            </div>
        </body>
        </html>
        <?php

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Erreur lors de la réservation: " . $e->getMessage();
    }
} else {
    header("Location: index.php");
}
exit();
