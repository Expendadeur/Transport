<?php
/**
 * Delete Ville
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();
authorize('admin');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM ville WHERE idVil = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "La ville a été supprimée.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Impossible de supprimer cette ville (elle peut être liée à des agences ou des adresses).";
    }
}

header("Location: index.php");
exit();
?>
