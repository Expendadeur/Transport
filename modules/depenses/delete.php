<?php
/**
 * Delete Dépense
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();
authorize('admin');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM depenses WHERE idDep = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "La dépense a été supprimée.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Impossible de supprimer cette dépense.";
    }
}

header("Location: index.php");
exit();
?>
