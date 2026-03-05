<?php
/**
 * Delete Réservation
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();
authorize('gestionnaire');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM reservation WHERE idRes = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "La réservation a été annulée.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Impossible d'annuler cette réservation.";
    }
}

header("Location: index.php");
exit();
?>
