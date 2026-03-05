<?php
/**
 * Delete Trajet
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();
authorize('gestionnaire');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM trajet WHERE id_Traj = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Le trajet a été supprimé.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Impossible de supprimer ce trajet (des véhicules ou réservations y sont liés).";
    }
}

header("Location: index.php");
exit();
?>
