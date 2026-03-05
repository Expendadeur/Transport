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
    $id_Trajet = (int)$_POST['id_Trajet'];
    $date_reservation = $_POST['date_reservation'];
    $nr_place = (int)$_POST['nr_place'];

    if (!$nom || !$telephone || !$id_Trajet) {
        die("Erreur: Données incomplètes.");
    }

    try {
        $pdo->beginTransaction();

        // 1. Check if passager exists by phone
        $stmt = $pdo->prepare("SELECT idP FROM passager WHERE telephone = ? LIMIT 1");
        $stmt->execute([$telephone]);
        $passager = $stmt->fetch();

        if ($passager) {
            $id_Passager = $passager['idP'];
        } else {
            // Create new passenger
            $stmt = $pdo->prepare("INSERT INTO passager (nomP, prenomP, telephone, pays) VALUES (?, ?, ?, 'Burundi')");
            $stmt->execute([$nom, $prenom, $telephone]);
            $id_Passager = $pdo->lastInsertId();
        }

        // 2. Insert reservation
        $stmt = $pdo->prepare("INSERT INTO reservation (date_reservation, nr_place, id_Passager, id_Trajet, id_Payment) VALUES (?, ?, ?, ?, NULL)");
        $stmt->execute([$date_reservation, $nr_place, $id_Passager, $id_Trajet]);
        $resId = $pdo->lastInsertId();

        $pdo->commit();

        // Show success screen
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <title>Succès - TRANSLOG</title>
            <link rel="stylesheet" href="assets/css/style.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <style>
                body { background: #f1f5f9; display: flex; align-items: center; justify-content: center; height: 100vh; }
                .success-card { background: white; padding: 3rem; border-radius: 20px; text-align: center; box-shadow: var(--shadow); max-width: 500px; }
            </style>
        </head>
        <body>
            <div class="success-card">
                <i class="fas fa-check-circle" style="font-size: 5rem; color: #16a34a; margin-bottom: 2rem;"></i>
                <h2 style="margin-bottom: 1rem;">Réservation Envoyée !</h2>
                <p style="color: #64748b; margin-bottom: 2rem;">Votre réservation #RES-<?php echo $resId; ?> est enregistrée. Veuillez vous présenter à l'agence pour le paiement et le retrait de votre ticket.</p>
                <a href="index.php" class="btn btn-primary">Retour à l'accueil</a>
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
