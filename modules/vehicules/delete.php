<?php
/**
 * Delete Véhicule
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();
authorize('gestionnaire');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM automobile WHERE id_aut = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Le véhicule a été supprimé.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Impossible de supprimer ce véhicule.";
    }
}

header("Location: index.php");
exit();
?>
