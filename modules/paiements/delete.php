<?php
/**
 * Delete Paiement
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();
authorize('admin');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM payment WHERE idPay = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Le paiement a été supprimé.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Impossible de supprimer ce paiement.";
    }
}

header("Location: index.php");
exit();
?>
