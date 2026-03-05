<?php
/**
 * Delete Client
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();
authorize('gestionnaire');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM passager WHERE idP = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Le client a été supprimé.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Impossible de supprimer ce client (il peut être lié à des réservations).";
    }
}

header("Location: index.php");
exit();
?>
