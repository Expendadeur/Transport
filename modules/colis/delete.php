<?php
/**
 * Delete Colis
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();
authorize('admin');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM courrier WHERE idCourrier = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "L'expédition a été supprimée des archives.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Impossible de supprimer ce colis.";
    }
}

header("Location: index.php");
exit();
?>
